<script setup>
  import { ref, watch, computed } from 'vue'
  import { useUserStore } from '@/stores/user.js'

  const props = defineProps({
    vcard: {
      type: Object,
      required: true
    },
    operationType: {
      type: String,
      default: 'insert'  // insert / update
    },
    errors: {
      type: Object,
      required: false,
    },
  })

  const emit = defineEmits(['save', 'cancel'])
  const userStore = useUserStore() 
  const flag = userStore.user?.user_type == 'A' ? false : true

  const editingVcard = ref(props.vcard)

  watch(
    () => props.vcard, 
    (newVcard) => { 
      editingVcard.value = newVcard
    }
  )

  const save = () => {
    emit('save', editingVcard.value)
  }

  const cancel = () => {
    emit('cancel', editingVcard.value)
  }

</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="save"
  >
    <h3 class="mt-5 mb-3">Vcard Details</h3>
    <hr>

    <div class="d-flex flex-wrap justify-content-between">
      <div class="mb-3 me-3 flex-grow-1">
        <label
          for="inputName"
          class="form-label"
        >Owner *</label>
        <input
          type="text"
          class="form-control"
          id="inputName"
          placeholder="Owner name"
          required
          v-model="editingVcard.name"
        >
        <field-error-message :errors="errors" fieldName="name"></field-error-message>
      </div>
      <div class="mb-3 me-3 flex-grow-1">
        <label
          for="inputEmail"
          class="form-label"
        >Email *</label>
        <input
          type="text"
          class="form-control"
          id="inputEmail"
          placeholder="Owner email"
          required
          v-model="editingVcard.email"
        >
        <field-error-message :errors="errors" fieldName="email"></field-error-message>
      </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between">
      <div class="mb-3 me-3 flex-grow-1">
        <label
          for="inputPhoneNumber"
          class="form-label"
        >Phone Number *</label>
        <input
        type="text"
        class="form-control"
        id="inputPhoneNumber"
        placeholder="Phone Number"
        required
        v-model="editingVcard.phone_number"
        :disabled="operationType != 'insert'">
      <field-error-message :errors="errors" fieldName="phone_number"></field-error-message>
      </div>

      <div class="mb-3 me-3 flex-grow-1" v-if="props.operationType == 'update'">
        <label
          for="inputBalance"
          class="form-label"
        >Balance</label>
        <input
        type="number"
        class="form-control"
        id="inputBalance"
        placeholder="Balance"
        required
        v-model="editingVcard.balance"
        disabled>
      </div>

      <div class="mb-3 me-3 flex-grow-1" v-if="props.operationType == 'update'">
        <label
          for="inputMaxDebit"
          class="form-label"
        >Max Debit</label>
        <input
        type="number"
        class="form-control"
        id="inputMaxDebit"
        placeholder="Max Debit"
        required
        v-model="editingVcard.max_debit"
        :disabled=flag>
        <field-error-message :errors="errors" fieldName="max_debit"></field-error-message>
      </div>

      <div class="mb-3 me-3 flex-grow-1 form-group" v-if="props.operationType == 'insert'">
        <label
          for="inputPassword"
          class="form-label"
        >Password *</label>
        <input
        type="password"
        class="form-control"
        id="inputPassword"
        placeholder="Password"
        required
        v-model="editingVcard.password"
      >
      <field-error-message :errors="errors" fieldName="password"></field-error-message>
      </div>

      <div class="mb-3 ms-xs-3 flex-grow-1 form-group" v-if="props.operationType == 'insert'">
        <label
          for="inputConfirmationCode"
          class="form-label"
        >Confirmation Code *</label>
        <input
        type="password"
        class="form-control"
        id="inputConfirmationCode"
        placeholder="Confirmation Code"
        required
        v-model="editingVcard.confirmation_code"
      >
      <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
      </div>
    </div>
    
    <div class="mb-3 d-flex justify-content-center ">
      <button
        type="button"
        class="btn btn-success px-5 mx-2"
        @click="save"
      >{{ props.operationType == 'update' ? 'Edit' : 'Create'}}</button>
      <button
        type="button"
        class="btn btn-dark px-5"
        @click="cancel"
      >Cancel</button>
    </div>

  </form>
</template>

<style scoped>
.total_price {
  width: 26rem;
}
.checkBilled {
  margin-top: 0.4rem;
  min-height: 2.375rem;
}
</style>
