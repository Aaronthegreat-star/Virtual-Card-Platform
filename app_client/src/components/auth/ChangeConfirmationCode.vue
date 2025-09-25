<script setup>
  import { useToast } from "vue-toastification"
  import { useRouter } from 'vue-router'
  import { useUserStore } from '../../stores/user.js'
  import { ref } from 'vue'

  const toast = useToast()
  const router = useRouter()
  const userStore = useUserStore()
  
  const confirmationCodes = ref({
        current_password: '',
        confirmation_code: '',
        confirmation_code_confirm: ''
    })

  const errors = ref(null)

  const emit = defineEmits(['changedConfirmationCode'])

  const changeConfirmationCode = async () => {
    try {
      await userStore.changeConfirmationCode(confirmationCodes.value)
      toast.success('Confirmation Code has been changed.')
      emit('changedConfirmationCode')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Confirmation Code has not been changed due to validation errors!')
      } else {
        toast.error('Confirmation Code has not been changed due to unknown server error!')
      }
    }
  }
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="changeConfirmationCode">
    <h3 class="mt-5 mb-3">Confirmation Code</h3>
    <hr>
    <div class="mb-3">
      <div class="mb-3">
        <label for="inputCurrentPassword" class="form-label">Current Password</label>
        <input type="password" class="form-control" id="inputCurrentPassword" required
          v-model="confirmationCodes.current_password">
        <field-error-message :errors="errors" fieldName="current_password"></field-error-message>
      </div>
    </div>
    <div class="mb-3">
      <div class="mb-3">
        <label for="inputConfirmationCode" class="form-label">New Confirmation Code</label>
        <input type="password" class="form-control" id="inputConfirmationCode" required v-model="confirmationCodes.confirmation_code">
        <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
      </div>
    </div>
    <div class="mb-3">
      <div class="mb-3">
        <label for="inputConfirmationCodeConfirm" class="form-label">Confirm the New Confirmation Code</label>
        <input type="password" class="form-control" id="inputConfirmationCodeConfirm" required
          v-model="confirmationCodes.confirmation_code_confirm">
        <field-error-message :errors="errors" fieldName="confirmation_code_confirm"></field-error-message>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center">
      <button type="button" class="btn btn-success px-5" @click="changeConfirmationCode">Change Confirmation Code</button>
    </div>
  </form>
</template>
