<script setup>
import axios from 'axios'
import { ref, watch, computed, onMounted, inject } from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import TransactionDetail from "./TransactionDetail.vue"
import { useToast } from "vue-toastification"
import { useUserStore } from '@/stores/user.js'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()
const socket = inject("socket")

const newTransaction = () => {
  return {
    id: null,
    vcard: '',
    date: '',
    datetime: '',
    type: '',
    value: '',
    old_balance: '',
    new_balance: '',
    payment_type: '',
    payment_reference: '',
    pair_vcard: null,
    pair_transaction: null,
    category_id: '',
    description: null,
    created_at: null,
    updated_at: null,
    deleted_at: null,
  }
}
const categories = ref([])
const transaction = ref(newTransaction())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
const flagUser = userStore.user.user_type == 'V' ? true : false
let originalValueStr = ''

const loadTransaction = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || id < 0) {
    transaction.value = newTransaction()
    transaction.value.vcard = flagUser ? userStore.user.id : ''
    transaction.value.type = operation.value == 'request' ? 'C' : operation.value == 'insert' && flagUser ? 'D' : ''
    transaction.value.payment_type = operation.value == 'request' ? 'VCARD' : ''
    transaction.value.custom_options = operation.value == 'request' ? '[{ "request": true }]' : ''
    originalValueStr = JSON.stringify(transaction.value)
  } else {
    try {
      const response = await axios.get('transactions/' + id)
      transaction.value = response.data.data
      transaction.value.category_id = transaction.value.category_id?.id == null ? null : transaction.value.category_id.id
      transaction.value.vcard = transaction.value.vcard.phone_number
      originalValueStr = JSON.stringify(transaction.value)
    } catch (error) {
      console.log(error)
    }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert' || operation.value == 'request') {
    try {
      const response = await axios.post('transactions', transaction.value)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
      const params = {
        transaction: response.data.data,
        user: userStore.user
      }
      if (operation.value == 'request'){
        socket.emit('newRequest', transaction.value )
        toast.success('Transaction was requested successfully.')
      }else{
        socket.emit('newTransaction', params)
        toast.success('Transaction #' + response.data.data.id + ' was created successfully.')
      }
      router.push('/transactions');
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error(error.response.data.error === undefined ? 'Transaction was not created due to validation errors!' : error.response.data.error)
      } else {
        toast.error('Transaction was not created due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('transactions/' + props.id, transaction.value)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + response.data.data.id + ' was edited successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction was not edited due to validation errors!')
      } else {
        toast.error('Transaction was not edited due to unknown server error!')
      }
    }
  }
}


const cancel = () => {
  originalValueStr = JSON.stringify(transaction.value)
  router.back()
}

const props = defineProps({
  id: {
    type: Number,
    default: null
  }
})

const operation = computed(() => {
  return props.id == -2 ? 'request' : (!props.id || (props.id < 0)) ? 'insert' : 'update'
})

watch(
  () => props.id,
  (newValue) => {
    loadTransaction(newValue)
  }, {
    immediate: true,
  }
)

onMounted(async () => {
  await loadTransaction(props.id);
  // post - user user.id / admin nada
  // put - user transaction.id / admin transaction.id

  if (operation.value == 'insert' && userStore.user.user_type == 'A')
    return

  categories.value = []
  const id = operation.value == 'update' ? transaction.value.vcard : userStore.user.id
  try {
    const response = await axios.get(`vcards/${id}/categories?paginate=0`)
    categories.value = response.data.data
  } catch (error) {
    console.log(error)
  }
});


let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null;
  let newValueStr = JSON.stringify(transaction.value);
  if (originalValueStr !== newValueStr) {
    nextCallBack = next;
    confirmationLeaveDialog.value.show();
  } else {
    next();
  }
});
</script>


<template>
  <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
  </confirmation-dialog>
  <TransactionDetail :operationType="operation" :transaction="transaction" :errors="errors" :categories="categories"
    @save="save" @cancel="cancel">
  </TransactionDetail>
</template>
