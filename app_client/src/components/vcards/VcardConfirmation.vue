<script setup>
  import { ref, watch, computed } from 'vue'
  import {useRouter} from 'vue-router'
  import axios from 'axios'

  import { useToast } from "vue-toastification"
  import { useUserStore } from '../../stores/user'

  const toast = useToast()
  const router = useRouter()
  const errors = ref(null)
  const userStore = useUserStore()
  const user_type = userStore.user.user_type

  const props = defineProps({
    phone_number: {
      type: Number,
      default: null
    }
  })

  const credentials = ref({
    password: '',
    confirmation_code: '',
    sameUser: props.phone_number == userStore.user.id ? true : false,
  })

  const destroy = async () => {
    try{
      const body = credentials.value
      const response = await axios.delete(`vcards/${props.phone_number}`, { data: { body } })
      if (user_type == 'A'){
        toast.success('Vcard ' + response.data.data.phone_number + ' was deleted successfully.')
        router.back()
      }else{
        userStore.user = null
        router.push('/login')
        toast.success('Vcard ' + response.data.data.phone_number + ' was deleted successfully.')
      }
    }catch(error){
      if (error.response.status == 422){
        if (error.response.data.error.password !== undefined || error.response.data.error.confirmation_code !== undefined){
          errors.value = error.response.data.error
          toast.error('Vcard was not deleted due to validation errors!')
        }else{
          errors.value = null
          toast.error(error.response.data.error)
        }
      } else {
        toast.error('Vcard was not deleted due to unknown server error!')
      }
    }
  }

  const cancel = () => {
    router.back()
  }

</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="destroy"
  >
  <h3 class="mt-5 mb-3">Delete Vcard</h3>
    <hr>
    <div class="d-flex flex-wrap justify-content-between">
      <div class="mb-3 me-3 flex-grow-1">
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
        v-model="credentials.password"
      >
      <field-error-message :errors="errors" fieldName="password"></field-error-message>
      </div>

      <div class="mb-3 ms-xs-3 flex-grow-1 form-group">
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
        v-model="credentials.confirmation_code"
      >
      <field-error-message :errors="errors" fieldName="confirmation_code"></field-error-message>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-center ">
      <button
        type="button"
        class="btn btn-danger px-5 mx-2"
        @click="destroy"
      >Delete</button>
      <button
        type="button"
        class="btn btn-dark px-5"
        @click="cancel"
      >Cancel</button>
    </div>
  </form>
</template>