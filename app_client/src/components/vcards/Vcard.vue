<script setup>
import axios from 'axios'
import { ref, watch, computed, onMounted, inject} from 'vue'
import VcardDetail from "./VcardDetail.vue"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { useToast } from "vue-toastification"
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const toast = useToast()
const router = useRouter()
const socket = inject("socket")

const newVcard = () => {
  return {
    phone_number: null,
    name: '',
    email: '',
    photo_url: '',
    password: '',
    confirmation_code: '',
    blocked: 0,
    balance: 0,
    max_debit: 2500,
    created_at: null,
    updated_at: null,
    deleted_at: null,
  }
}

  const vcard = ref(newVcard())  
  const errors = ref(null)
  const confirmationLeaveDialog = ref(null)
  let originalValueStr = ''

const loadVcard = async (phone_number) => {
  originalValueStr = ''
  errors.value = null
  if (!phone_number || phone_number < 0) {
    vcard.value = newVcard()
    originalValueStr = JSON.stringify(vcard.value)
  } else {
    try {
      const response = await axios.get('vcards/' + phone_number)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
    } catch (error) {
      console.log(error)
    }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    try {
      const credentials = {
        username: vcard.value.phone_number,
        password: vcard.value.password
      }
      const response = await axios.post('vcards', vcard.value)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      socket.emit('insertVcard', response.data.data)
      toast.success('Vcard #' + response.data.data.phone_number + ' was created successfully.')
      if (userStore.user == null){
        await userStore.login(credentials)
        router.push('/dashboard')
        toast.success(userStore.user.name + ' has entered the application.')
      }else{
        router.back()
      } 
      
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error(error.response.data.error === undefined ? 'Vcard was not created due to validation errors!' : error.response.data.error )
      } else {
        toast.error('Vcard was not created due to unknown server error!')
      }
    }
  } else {
    try {
      vcard.value.max_debit = parseInt(vcard.value.max_debit)
      const response = await axios.put('vcards/' + props.phone_number, vcard.value)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      console.dir(response.data.data)
      socket.emit('updateVcard', response.data.data)
      toast.success('Vcard #' + response.data.data.phone_number + ' was edited successfully.')
      if (userStore.user.user_type == 'V')
        userStore.user.name = vcard.value.name
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Vcard was not edited due to validation errors!')
      } else {
        toast.error('Vcard was not edited due to unknown server error!')
      }
    }
  }
}


const cancel = () => {
  originalValueStr = JSON.stringify(vcard.value)  
  router.back()
}

const props = defineProps({
  phone_number: {
    type: Number,
    default: null
  }
})

const operation = computed(() => {
  return (!props.phone_number || (props.phone_number < 0)) ? 'insert' : 'update'
})

watch(
  () => props.phone_number,
  (newValue) => {
    loadVcard(newValue)
  }, {
  immediate: true,
}
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(vcard.value)
  if (originalValueStr !== newValueStr) {
    nextCallBack = next
    confirmationLeaveDialog.value.show()
  } else {
    next()
  }
})
</script>


<template>
  <confirmation-dialog
    ref="confirmationLeaveDialog"
    confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!"
    @confirmed="leaveConfirmed"
  >
  </confirmation-dialog>  
  <VcardDetail 
    :operationType="operation" 
    :vcard="vcard" 
    :errors="errors" 
    @save="save" 
    @cancel="cancel">
  </VcardDetail>
</template>
