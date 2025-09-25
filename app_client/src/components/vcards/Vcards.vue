<script setup>
  import axios from 'axios'
  import { ref, watchEffect, computed, onMounted, inject } from 'vue'
  import {useRouter} from 'vue-router'
  import VcardTable from "./VcardTable.vue"
  import { Bootstrap5Pagination } from 'laravel-vue-pagination';

  import { useToast } from "vue-toastification"
  const toast = useToast()

  const paginationData = ref({})
  const router = useRouter();
  const vcards = ref([])
  const users = ref([])
  const filterByOwnerId = ref(null)
  const filterByBlocked = ref(null)
  const orderBy = ref(null)
  const totalVcards = ref(null)
  const socket = inject("socket")

  const loadVcards = async (page = 1) => {
    try{
      const response = await axios.get('vcards', {params: {
        page: page,
        owner: filterByOwnerId.value,
        blocked: filterByBlocked.value,
        order: orderBy.value,
      }})
      vcards.value = response.data.data
      paginationData.value = response.data
      totalVcards.value = paginationData.value.meta.total
    }catch(error){
      console.log(error)
    }
  }

  const loadUsers = async () => {
    try{
      const response = await axios.get('users', {params:{paginate: 0, userType: 'V',}})
      users.value = response.data.data
    }catch(error){
      console.log(error)
    }
  }

  const addVcard = () => {
      router.push({ name: 'NewVcard'})
  }
  
  const editVcard = (vcard) => {
      router.push({name: 'Vcard', params: { phone_number: vcard.phone_number }})
  }

  const deleteVcard = async (vcard) => { 
    router.push({name: 'VcardConfirmation', params: { phone_number: vcard.phone_number }})
  }

  const blockVcard = async (vcard) => {
    let blocked = vcard.blocked ? 'unblocked' : 'blocked'
    try{
      const response = await axios.patch('vcards/' + vcard.phone_number + '/blocked', { blocked: vcard.blocked ? '0' : '1' })
      toast.success('Vcard ' + response.data.data.phone_number + ' was ' + blocked + ' successfully.')
      let blockedVcard = response.data.data
      let idx = vcards.value.findIndex((t) => t.phone_number === blockedVcard.phone_number)
      if (idx >= 0)
        vcards.value[idx].blocked = vcard.blocked ? 0 : 1
    }catch(error){
      toast.error('Vcard was not ' + blocked + ' due to unknown server error!')
    }
  }

  socket.on('insertVcard', () => { 
    loadUsers()
  })

  

  watchEffect(
  () => {
    loadVcards()
  })

  onMounted(() => {
    loadUsers()
    loadVcards()
  })

</script>

<template>
  <div class="d-flex justify-content-between">
    <div class="mx-2">
      <h3 class="mt-4">Vcards</h3>
    </div>
    <div class="mx-2 total-filtro">
      <h5 class="mt-4">Total: {{ totalVcards }}</h5>
    </div>
  </div>
  <hr>
  <div class="mb-3 d-flex justify-content-between flex-wrap">
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectOwner"
        class="form-label"
      >Filter by owner:</label>
      <select
        class="form-select"
        id="selectOwner"
        v-model="filterByOwnerId"
      >
        <option :value="null"></option>
        <option
          v-for="user in users"
          :key="user.id"
          :value="user.id"
        >{{user.name}}</option>
      </select>
    </div>
    <div class="mx-2 mt-2 flex-grow-1 filter-div">
      <label
        for="selectStatus"
        class="form-label"
      >Filter by blocked:</label>
      <select
        class="form-select"
        id="selectStatus"
        v-model="filterByBlocked"
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
        <option value="desc">Recent</option>
        <option value="asc">Oldest</option>
      </select>
    </div>
    <div class="mx-2 mt-2">
      <button
        type="button"
        class="btn btn-success px-4 btn-addprj"
        @click="addVcard"
      ><i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Vcard</button>
    </div>
  </div>
  <vcard-table
    :vcards="vcards"
    :showId="true"
    :showDates="true"
    @edit="editVcard"
    @delete="deleteVcard"
    @block="blockVcard"
  ></vcard-table>
  <Bootstrap5Pagination
  :data="paginationData"
  @pagination-change-page="loadVcards"
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
