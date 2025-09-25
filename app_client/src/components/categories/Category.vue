<script setup>
  import axios from 'axios'
  import { ref, watch, computed, onMounted} from 'vue'
  import CategoryDetail from "./CategoryDetail.vue"
  import { useUserStore } from '@/stores/user.js'
  import { useRouter, onBeforeRouteLeave } from 'vue-router'
  import { useToast } from "vue-toastification"

  const toast = useToast()
  const router = useRouter()
  const userStore = useUserStore()

  const newCategory = () => {
    return {
      id: null,
      vcard: null,
      type: '',
      name: '',
      custom_options: '',
      custom_data: '',
      deleted_at: null,
    }
  }
  
  const flag = userStore.user.user_type == 'V' ? true : false 
  const category = ref(newCategory()) 
  const errors = ref(null)
  const confirmationLeaveDialog = ref(null)
  let originalValueStr = ''

  const loadCategory = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || id < 0) {
    category.value = newCategory()
    originalValueStr = JSON.stringify(category.value)
  } else {
    try {
      const response = flag ? await axios.get('categories/' + id) : await axios.get('defaultCategories/' + id)
      category.value = response.data.data
      originalValueStr = JSON.stringify(category.value)
    } catch (error) {
      console.log(error)
    }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert'){
      	category.value.vcard = flag ? userStore.user.id : ''
      try{
        const response = flag ? await axios.post('categories', category.value) : await axios.post('defaultCategories', category.value)
        category.value = response.data.data
        originalValueStr = JSON.stringify(category.value)
        toast.success('Category #' + response.data.data.id + ' was created successfully.')
        router.back();
      }catch(error){
        if (error.response.status == 422) {
          errors.value = error.response.data.errors
          toast.error('Category was not created - it already exists!')
        } else {
          toast.error('Category was not created due to unknown server error!')
        }
      }
  }else{
    try{
      category.value.vcard = flag ? userStore.user.id : ''
      const response = flag ? await axios.put('categories/' + props.id, category.value) : await axios.put('defaultCategories/' + props.id, category.value)
      category.value = response.data.data
      originalValueStr = JSON.stringify(category.value)
      toast.success('Category #' + response.data.data.id + ' was edited successfully.')
      router.back()
    }catch(error){
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Category was not edited - it already exists!')
      } else {
        toast.error('Category was not edited due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(category.value)  
  router.back()
}

const props = defineProps({
  id: {
    type: Number,
    default: null
  }
})

const operation = computed(() => {
  return (!props.id || (props.id < 0)) ? 'insert' : 'update'
})

watch(
  () => props.id,
  (newValue) => {
    loadCategory(newValue)
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
  let newValueStr = JSON.stringify(category.value)
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
  <CategoryDetail 
    :operationType="operation" 
    :category="category" 
    :errors="errors" 
    @save="save" 
    @cancel="cancel">
  </CategoryDetail>
</template>
