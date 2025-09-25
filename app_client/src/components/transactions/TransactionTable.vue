<script setup>

const props = defineProps({
  transactions: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showVcard: {
    type: Boolean,
    default: true,
  },
  showDateTime: {
    type: Boolean,
    default: true,
  },
  showType: {
    type: Boolean,
    default: true,
  },
  showValue: {
    type: Boolean,
    default: true,
  },
  showOldBalance: {
    type: Boolean,
    default: true,
  },
  showNewBalance: {
    type: Boolean,
    default: true,
  },
  showPaymentType: {
    type: Boolean,
    default: true,
  },
  showDescription: {
    type: Boolean,
    default: false,
  },
  showPaymentReference: {
    type: Boolean,
    default: true,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
  showCategory: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
  showCreatePDFButton: {
    type: Boolean,
    default: true,
  },
  showConfirmTransactionButton: {
    type: Boolean,
    default: true,
  }
})

const emit = defineEmits(['edit', 'delete', 'createPDF', 'confirmTransaction'])

const editClick = (transaction) => {
  emit('edit', transaction)
}

const deleteClick = (transaction) => {
  emit('delete', transaction)
}

const createPDFClick = (transaction) => {
  emit('createPDF', transaction)
}

const confirmTransactionClick = (transaction) => {
  emit('confirmTransaction', transaction)
}

</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showId">#</th>
        <th>Vcard</th>
        <th v-if="showDateTime">Date</th>
        <th v-if="showType">Type</th>
        <th v-if="showValue">Value</th>
        <th v-if="showOldBalance">Old Balance</th>
        <th v-if="showNewBalance">New Balance</th>
        <th v-if="showPaymentType">Payment Type</th>
        <th v-if="showPaymentReference">Payment Reference</th>
        <th v-if="showCategory">Category</th>
        <th v-if="showDescription">Description</th>
        <th v-if="showEditButton || showDeleteButton || showCreatePDFButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="transaction in transactions" :key="transaction.id">
        <td class="text-center" v-if="showId">{{ transaction.custom_options == null ? transaction.id : 'REQUEST - ' + transaction.id}}</td>
        <td>{{ transaction.vcard.phone_number }}</td>
        <td v-if="showDateTime">{{ transaction.datetime }}</td>
        <td v-if="showType">{{ transaction.type == 'C' ? 'Credit' : 'Debit' }}</td>
        <td v-if="showValue" :class="transaction.type == 'C' ? 'text-success' : 'text-danger'">
          {{ transaction.type == 'C' ? '+ ' + transaction.value : '- ' + transaction.value }}</td>
        <td v-if="showOldBalance">{{ transaction.custom_options == null ? transaction.old_balance + ' €' : ''}}</td>
        <td v-if="showNewBalance">{{ transaction.custom_options == null ? transaction.new_balance + ' €' : ''}}</td>
        <td v-if="showPaymentType">{{ transaction.payment_type }}</td>
        <td v-if="showPaymentReference">{{ transaction.payment_reference }}</td>
        <td v-if="showCategory">
          {{ transaction.category_id ? transaction.category_id.name : 'No Category' }}
        </td>

        <td v-if="showDescription">{{ transaction.description }}</td>
        <td class="text-end" v-if="showEditButton || showDeleteButton || showCreatePDFButton">
          <div v-if="transaction.custom_options == null" class="d-flex justify-content-center">
            <button class="btn btn-xs btn-light" @click="editClick(transaction)" v-if="showEditButton"><i
                class="bi bi-xs bi-pencil"></i>
            </button>

            <button class="btn btn-xs btn-light" @click="deleteClick(transaction)" v-if="showDeleteButton"><i
                class="bi bi-xs bi-x-square-fill"></i>
            </button>

            <button class="btn btn-xs btn-light" @click="createPDFClick(transaction)" v-if="showCreatePDFButton">
              <i class="bi bi-xs bi-filetype-pdf"></i>
            </button>
          </div>
          <div v-else class="d-flex justify-content-center">
            <button class="btn btn-xs btn-light" @click="confirmTransactionClick(transaction)" v-if="showConfirmTransactionButton">
              <i class="bi bi-xs bi-check-circle"></i>
            </button>

            <button class="btn btn-xs btn-light" @click="deleteClick(transaction)" v-if="showDeleteButton"><i
                class="bi bi-xs bi-x-square-fill"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>


