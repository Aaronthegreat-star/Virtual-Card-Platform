<script setup>
import axios from 'axios'
import UserDetail from "./UserDetail.vue"
import { useToast } from "vue-toastification"
import { useUserStore } from '../../stores/user.js'
import { ref, watch, inject, computed} from 'vue'
import { useRouter, onBeforeRouteLeave } from 'vue-router'

const socket = inject("socket")
const toast = useToast()
const router = useRouter()
const errors = ref([]) 
const userStore = useUserStore()
const confirmationLeaveDialog = ref(null)
let originalValueStr = ''

const props = defineProps({
  id: {
    type: Number,
    default: null
  }
})

const newUser = () => {
  return {
    id: null,
    name: '',
    email: '',
    password: '',
    photo_url: null
  }
}

const loadUser = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    user.value = newUser()
    // so se pode criar admins
    user.value.user_type = 'A'
  } else {
      try {
        const response = await axios.get('users/' + id)
        user.value = response.data.data
        originalValueStr = JSON.stringify(user.value)
      } catch (error) {
        console.log(error)
      }
  }
}

const save = async () => {
  errors.value = null;

  if (operation.value == 'insert'){
    try {
      const response = await axios.post('admins', user.value)
      user.value = response.data.data;
      originalValueStr = JSON.stringify(user.value);
      toast.success('Admin ' + user.value.name + ' was created successfully.');
      if (user.value.id == userStore.userId) {
        await userStore.loadUser();
      }
      router.back();
    } catch (error) {
      if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors;
        toast.error('Admin was not created due to validation errors!');
      } else {
        toast.error('Admin was not created due to an unknown server error!');
      }
    }
  }else{
    try {
      const response = user.value.user_type == 'A' ? await axios.put('admins/' + props.id, user.value) : await axios.patch('vcards/' + props.id + '/profile', user.value)
      user.value = response.data.data;
      originalValueStr = JSON.stringify(user.value);
      socket.emit('updateVcard', response.data.data)
      toast.success('User ' + user.value.name + ' was updated successfully.')

      if (userStore.user.user_type == 'A'){
        if (userStore.user.id == user.value.id){
          userStore.user.name = user.value.name
        }
      }

      if (userStore.user.user_type == 'V'){
        userStore.user.name == user.value.name
      }

      if (user.value.phone_number == userStore.user.id) {
        await userStore.loadUser();
      }
      
      router.back();
      
    } catch (error) {
      if (error.response && error.response.status === 422) {
        errors.value = error.response.data.errors;
        toast.error('User #' + props.id + ' was not updated due to validation errors!');
      } else {
        toast.error('User #' + props.id + ' was not updated due to an unknown server error!');
      }
    }
  }
  
};

const cancel = () => {
  originalValueStr = JSON.stringify(user.value)
  router.back()
}

const user = ref(newUser())

const operation = computed(() => {
  return (!props.id || (props.id < 0)) ? 'insert' : 'update'
})


watch(
  () => props.id,
  (newValue) => {
    loadUser(newValue)
  },
  { immediate: true }
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(user.value)
  if (originalValueStr != newValueStr) {
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
  <user-detail 
  :user="user" 
  :operation="operation"
  :errors="errors"
  @save="save" 
  @cancel="cancel">
  </user-detail>
</template>
