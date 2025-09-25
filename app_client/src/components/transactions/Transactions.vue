<script setup>
import axios from 'axios'
import { ref, watchEffect, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import TransactionTable from "./TransactionTable.vue"
import { useToast } from "vue-toastification"
import { useUserStore } from '@/stores/user.js'
import { Bootstrap5Pagination } from 'laravel-vue-pagination';

const toast = useToast();
const router = useRouter();
const paginationData = ref({})
const userStore = useUserStore()
const flag = userStore.user.user_type == 'A' ? false : true
const totalTransactions = ref(null)
const transactions = ref([])
const categories = ref([])
const filterByType = ref(null)
const filterByPaymentType = ref(null)
const filterByCategory = ref(null)
const filterByRequested = ref(null)
const orderBy = ref(null)
const socket = inject("socket")

const loadTransactions = async (page = 1) => {
  try {
    const params = {
      page: page,
      type: filterByType.value,
      payment: filterByPaymentType.value,
      category: filterByCategory.value,
      order: orderBy.value,
      requested: filterByRequested.value,
    }
    const response = flag ? await axios.get(`vcards/${userStore.user.id}/transactions`, { params: params }) : await axios.get('transactions', { params: params })
    transactions.value = response.data.data
    paginationData.value = response.data
    totalTransactions.value = paginationData.value.meta.total
  } catch (error) {
    console.log(error)
  }
}

const loadCategories = async () => {
  if (!flag)
    return

  try {
    const response = await axios.get(`vcards/${userStore.user.id}/categories?paginate=0`)
    categories.value = response.data.data
  } catch (error) {
    console.log(error)
  }
}

const editTransaction = (transaction) => {
  router.push({ name: 'Transaction', params: { id: transaction.id } })
}

const pdfTransaction = async (transaction) => {
  try {
    const response = await axios.get(`/transactions/${transaction.id}/pdf`, {
      responseType: 'blob',
    });

    const blob = new Blob([response.data], { type: 'application/pdf' });

    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Receipt_${transaction.date}.pdf`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error generating PDF:', error);
  }
};


  const deleteTransaction = async (transaction, flag=true) => {
    
    try{
      const response = await axios.delete('transactions/' + transaction.id)
      let deletedTransaction = response.data.data
      let idx = transactions.value.findIndex((t) => t.id === deletedTransaction.id)
      if (idx >= 0) {
        transactions.value.splice(idx, 1)
      }

      if(flag){
        toast.success('Transaction #' + response.data.data.id + ' was deleted successfully.')
      }

      if (flag && deletedTransaction.custom_options != null){
        socket.emit('cancelRequest', deletedTransaction)
      }
      
    }catch(error){
      if (error.response.status == 422){
        toast.error("Can't delete Transaction - Vcard exists")
      }else {
        toast.error("Transaction wasn't deleted due to unknown server error!")
      }
    }
  }

  const confirmTransaction = async (transaction) => {

    const originalTransaction = transaction
    transaction.custom_options = null
    transaction.vcard = transaction.vcard.phone_number
    try {
      const response = await axios.post('transactions', transaction)
      socket.emit('newRequest', response.data.data)
      await deleteTransaction(originalTransaction, false)

      toast.success('Transaction was accepted successfully.')

    } catch (error) {
      toast.error('Transaction was not accepted.')
    }
  }

  socket.on('newTransaction', () => { 
    loadTransactions()
  })

  socket.on('newRequest', () => { 
    loadTransactions()
  })

watchEffect(
  () => {
    loadTransactions()
  })

onMounted(() => {
  loadTransactions()
  loadCategories()
})

</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Transactions</h3>
    </div>
    <div class="mx-2 total-filtro">
      <h5 class="mt-4">Total: {{ totalTransactions }}</h5>
    </div>
  </div>
  <hr>
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectType" class="form-label">Filter by Type:</label>
      <select class="form-select" id="selectType" v-model="filterByType">
        <option :value="null"></option>
        <option value="C">Credit</option>
        <option value="D">Debit</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectStatus" class="form-label">Filter by Payment Type:</label>
      <select class="form-select" id="selectStatus" v-model="filterByPaymentType">
        <option :value="null"></option>
        <option value="VCARD">VCARD</option>
        <option value="MBWAY">MBWAY</option>
        <option value="PAYPAL">PAYPAL</option>
        <option value="IBAN">IBAN</option>
        <option value="MB">MB</option>
        <option value="VISA">VISA</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div" v-if="flag">
      <label for="selectCategory" class="form-label">Filter by Category:</label>
      <select class="form-select" id="selectCategory" v-model="filterByCategory">
        <option :value="null"></option>
        <option value='none'>No Category</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectRequested" class="form-label">Filter By Requested:</label>
      <select class="form-select" id="selectRequested" v-model="filterByRequested">
        <option :value="null"></option>
        <option value='req'>Requested</option>
        <option value='nreq'>Not Requested</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label for="selectOrderBy" class="form-label">Order by:</label>
      <select class="form-select" id="selectOrderBy" v-model="orderBy">
        <option :value="null"></option>
        <option value="desc">Recent</option>
        <option value="asc">Oldest</option>
        <option value="pasc">Value - Asc</option>
        <option value="pdesc">Value - Desc</option>
      </select>
    </div>
  </div>
  <transaction-table :transactions="transactions" :showId="true" :showDates="true" :showEditButton="flag" :showDeleteButton="flag" @edit="editTransaction"
    @delete="deleteTransaction" @createPDF="pdfTransaction" @confirmTransaction="confirmTransaction"></transaction-table>
  <Bootstrap5Pagination :data="paginationData" @pagination-change-page="loadTransactions" :limit="2">
  </Bootstrap5Pagination>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}

.total-filtro {
  margin-top: 0.35rem;
}

.btn-addprj {
  margin-top: 1.85rem;
}
</style>
