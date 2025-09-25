<script setup>

import axios from 'axios'
import { ref, watch, computed, onMounted } from 'vue'
import { useUserStore } from '@/stores/user.js'
import {useRouter} from 'vue-router'
import CategoryTable from "./CategoryTable.vue"
import { useToast } from "vue-toastification"
import { Bootstrap5Pagination } from 'laravel-vue-pagination';

const toast = useToast()
const categories = ref([])
const userStore = useUserStore()
const router = useRouter()
const paginationData = ref({})
const totalCategories = ref(null)

const flag = userStore.user.user_type == 'V' ? true : false

const loadCategories = async (page = 1) => {
    try{
        const response = flag ? await axios.get(`vcards/${userStore.user.id}/categories`, {params: {page: page, paginate: 1}}) 
        : await axios.get(`defaultCategories?page=${page}`)
        categories.value = response.data.data
        paginationData.value = response.data
        totalCategories.value = paginationData.value.meta.total
    }catch(error){
        console.log(error)
    }
}

const addVcard = () => {
      router.push({ name: 'NewCategory'})
  }

const editCategory = async (category) => {
  router.push({name: 'Category', params: { id: category.id }})
}

const deleteCategory = async (category) => {
  try{
      const response = flag ? await axios.delete('categories/' + category.id) : await axios.delete('defaultCategories/' + category.id)
      let deletedCategory = response.data.data
      let idx = categories.value.findIndex((t) => t.id === deletedCategory.id)
      if (idx >= 0) {
        categories.value.splice(idx, 1)
      }
      toast.success('Category ' + response.data.data.name + ' was deleted successfully.')
    }catch(error){
      if (error.response.status == 422){
      toast.error("Can't delete Category due to validation issues")
      }else {
        toast.error("Category wasn't deleted due to unknown server error!")
      }
    }
}

onMounted(() => {
    loadCategories()
})

</script>

<template>
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Categories</h3>
      </div>
      <div class="mx-2 total-filtro">
        <h5 class="mt-4">Total: {{totalCategories}}</h5>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-end flex-wrap">
        <div class="mx-2 mt-2">
          <button
            type="button"
            class="btn btn-success px-4 btn-addprj"
            @click="addVcard"
          ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Category</button>
        </div>
      </div>
    <hr> 
    <category-table
    :categories="categories"
    :showVcardNumber="flag"
    :showOwner="flag"
    @edit="editCategory"
    @delete="deleteCategory"
  ></category-table>
  <Bootstrap5Pagination
  :data="paginationData"
  @pagination-change-page="loadCategories"
  :limit="2">
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