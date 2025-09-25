<script setup>
  import axios from 'axios'
  import { ref, watchEffect, computed, onMounted, inject } from 'vue'
  import { useToast } from "vue-toastification"
  import {useRouter} from 'vue-router'
  import UserTable from "./UserTable.vue"
  import { Bootstrap5Pagination } from 'laravel-vue-pagination';

  const users = ref([])
  const paginationData = ref({})
  const router = useRouter()
  const toast = useToast()
  const socket = inject("socket")

  const filterTypeOfUser = ref(null)
  const filterBlockedUser = ref(null)
  const totalUsers = ref(null)
  const orderBy = ref(null)

  const loadUsers = async (page = 1) => {

    try{
      const response = await axios.get('users', {params:{
        page: page, 
        paginate: 1,
        userType: filterTypeOfUser.value,
        blocked: filterBlockedUser.value,
        order: orderBy.value
      }})
      users.value = response.data.data
      paginationData.value = response.data
      totalUsers.value = paginationData.value.meta.total
    }catch(error){
      console.log(error)
    }
  }

  const editUser = (user) => {
      router.push({name: 'User', params: { id: user.id }})
  }

  const deleteUser = async (user) => {
    try{
      const response = await axios.delete('admins/' + user.id)
      let deletedUser = response.data.data
      let idx = users.value.findIndex((u) => u.id === deletedUser.id)
      if (idx >= 0) {
        users.value.splice(idx, 1)
      }
      toast.success('User ' + response.data.data.name + ' was deleted successfully.')
    }catch(error){
      toast.error("User wasn't deleted due to unknown server error!")
    }
  }

  const addAdmin = () => {
      router.push({ name: 'NewUser'})
  }

  socket.on('updateVcard', () => {
    loadUsers()
  })

  socket.on('insertVcard', () => {
    loadUsers()
  })

  watchEffect(
  () => {
    loadUsers()
  })

  onMounted (() => {
    loadUsers()
  })

</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Users</h3>
    </div>
    <div class="mx-2 total-filtro">
      <h5 class="mt-4">Total: {{ totalUsers }}</h5>
    </div>
  </div>
  <div class="mb-3 d-flex justify-content-end flex-wrap">
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4 btn-addprj"
        @click="addAdmin"
        ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Admin</button>
    </div>
  </div>
  <hr>
  <div class="mb-3 d-flex justify-content-start flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectTypeOfUser"
        class="form-label"
      >Type of User:</label>
      <select
        class="form-select"
        id="selectTypeOfUser"
        v-model="filterTypeOfUser"
      >
        <option :value="null"></option>
        <option value="A">Administrator</option>
        <option value="V">Vcard Owner</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectBlockedUser"
        class="form-label"
      >Blocked:</label>
      <select
        class="form-select"
        id="selectBlockedUser"
        v-model="filterBlockedUser"
      >
        <option :value="null"></option>
        <option value="1">Blocked</option>
        <option value="0">Not Blocked</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectOrderBy"
        class="form-label"
      >Order by:</label>
      <select
        class="form-select"
        id="selectOrderBy"
        v-model="orderBy"
      >
        <option :value="null"></option>
        <option value="asc">Alphabetically - Asc</option>
        <option value="desc">Alphabetically - Desc</option>
      </select>
    </div>
  </div>
  <user-table
    :users="users"
    :showId="false"
    @edit="editUser"
    @delete="deleteUser"
  ></user-table>
  <Bootstrap5Pagination
  :data="paginationData"
  @pagination-change-page="loadUsers"
  :limit="1">
  </Bootstrap5Pagination>
</template>

<style scoped>
.filter-div {
  min-width: 12rem;
}
.total-filtro {
  margin-top: 2.3rem;
}
</style>

