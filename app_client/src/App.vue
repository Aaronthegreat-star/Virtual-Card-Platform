<script setup>
import { useRouter, RouterLink, RouterView } from 'vue-router'
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from './stores/user.js'
import { onMounted , inject} from 'vue'

const userStore = useUserStore() 
const toast = useToast()
const router = useRouter()
const socket = inject("socket")

const logout = async () => {
  if (await userStore.logout()) {
    toast.success('User has logged out of the application.')
    clickMenuOption()
    router.push({name: 'home'})
  } else {
    toast.error('There was a problem logging out of the application!')
  }
}

const clickMenuOption = () => {
  const domReference = document.getElementById('buttonSidebarExpandId')
  if (domReference) {
    if (window.getComputedStyle(domReference).display !== "none") {
      domReference.click()
    }
  }
  const routeName = router.currentRoute.value.name;

  if (routeName === 'Payments') {
    router.push({ name: 'NewTransaction' });
  }
}

socket.on('newTransaction', (params) => {
    if (params.user.user_type == 'A'){
      toast.success(`A new Transaction was made for you ! Value:${params.transaction.value} € (#${params.transaction.id} by ${params.user.name})`)
    }else {
      toast.success(`A new Transaction was made for you ! Value:${params.transaction.value} € (#${params.transaction.id} by ${params.transaction.vcard.phone_number})`)
    }
})

socket.on('newRequest', (transaction) => {
  if (transaction.custom_options != null){
    toast.success(`A new Transaction was requested for you !`)
  }else{
    toast.success(`Your Transaction Request was accepted !`)}
  
})

socket.on('cancelRequest', () => {
    toast.error(`Your Transaction Request was declined !`)
})

socket.on('insertVcard', (vcard) => {
    toast.success(`A new Vcard was create ! # ${vcard.phone_number} (${vcard.name})`)
})

socket.on('updateVcard', (vcard) => {
  if (userStore.userId == vcard.phone_number) {
    userStore.loadUser()
    toast.info('Your user profile has been changed!')
  } else {
    toast.info(`User profile #${vcard.phone_number} (${vcard.name}) has changed!`)
  }
})



</script>


<template>
  <nav class="navbar navbar-expand-md navbar-dark bg-dark sticky-top flex-md-nowrap p-0 shadow-lg">
    <div class="container-fluid">
      
      <router-link class="navbar-brand col-md-3 col-lg-2 me-0 px-3 bg-dark shadow-none" :to="{ name: 'home' }">
        <img src="@/assets/logo.svg" alt="" width="30" height="24" class="d-inline-block align-text-top">
        Vcards
      </router-link>
      <button id="buttonSidebarExpandId" class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item" v-show="!userStore.user">
            <router-link class="nav-link" :class="{active: $route.name === 'NewVcard'}" :to="{ name: 'NewVcard' }" aria-label="Add a new Vcard" @click="clickMenuOption">
              <i class="bi bi-person-check-fill"></i>
              Register
            </router-link>
          </li>
          <li class="nav-item" v-show="!userStore.user">
            <router-link class="nav-link" :class="{active: $route.name === 'Login'}" :to="{ name: 'Login'}" @click="clickMenuOption">
              <i class="bi bi-house"></i>
              Login
            </router-link>
          </li>
          <li class="nav-item dropdown" v-show="userStore.user">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <img :src="userStore.userPhotoUrl" class="rounded-circle z-depth-0 avatar-img" alt="avatar image">
              <span class="avatar-text">{{ userStore.userName }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end " aria-labelledby="navbarDropdownMenuLink">
              <li>
                <a class="dropdown-item" href="#">
                  <router-link class="nav-link"
                              :class="{ active: $route.name == 'User' && $route.params.id == userStore.userId }" 
                              :to="{ name: 'User', params: { id: userStore.userId } }" @click="clickMenuOption">
                  <i class="bi bi-person-square"></i>
                  Profile
                  </router-link>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'ChangePassword'}" :to="{ name: 'ChangePassword'}" @click="clickMenuOption">
                    <i class="bi bi-key"></i>
                    Change Password
                  </router-link>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" v-if="userStore.user?.user_type == 'V'">
                  <router-link class="nav-link" :class="{active: $route.name === 'ChangeConfirmationCode'}" :to="{ name: 'ChangeConfirmationCode'}" @click="clickMenuOption">
                    <i class="bi bi-key"></i>
                    Change Confirmation Code
                  </router-link>
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item" @click.prevent="logout"><i class="bi bi-arrow-right"></i>Logout</a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <div class="position-sticky pt-0">
          <ul class="nav flex-column">
            <li class="nav-item">
              <router-link class="nav-link" :class="{active: $route.name === 'Dashboard'}" :to="{ name: 'Dashboard'}" v-if="userStore.user" @click="clickMenuOption">
                <i class="bi bi-house"></i>
                  Dashboard
              </router-link>
            </li>
            <li class="nav-item d-flex justify-content-between align-items-center pe-3">
              <router-link class="nav-link w-100 me-3" :class="{ active: $route.name === 'Vcards' }" :to="{ name: 'Vcards' }" v-show="userStore.user?.user_type == 'A'"
              @click="clickMenuOption">
                <i class="bi bi-list-check"></i>
                Vcards
              </router-link>
              <router-link class="link-secondary" :to="{ name: 'NewVcard' }" aria-label="Add a new Vcard" v-show="userStore.user?.user_type == 'A'"
              @click="clickMenuOption">
                <i class="bi bi-xs bi-plus-circle"></i>
              </router-link>
            </li>
            <li class="nav-item" v-if="userStore.user">
              <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'NewTransaction'}" :to="{ name: 'NewTransaction'}" @click="() => clickMenuOption('Payments')">
                    <i class="bi bi-cash"></i>
                    Payment
                  </router-link>
                </a>
            </li>
            <li class="nav-item" v-if="userStore.user?.user_type == 'V'">
              <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'RequestTransaction'}" :to="{ name: 'RequestTransaction'}" @click="() => clickMenuOption('Payments')">
                    <i class="bi bi-send-arrow-up"></i>
                    Request Transaction
                  </router-link>
                </a>
            </li>
            <li class="nav-item" v-if="userStore.user">
              <router-link class="nav-link" :class="{active: $route.name === 'Categories'}" :to="{ name: 'Categories'}" @click="() => clickMenuOption('Categories')">
                <i class="bi bi-bookmarks"></i>
                Categories
              </router-link>
            </li>
            <li class="nav-item" v-show="userStore.user?.user_type == 'A'">
              <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'Users'}" :to="{ name: 'Users'}" @click="clickMenuOption">
                    <i class="bi bi-people"></i>
                    Users
                  </router-link>
                </a>
            </li>
          </ul>

          <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted" v-if="userStore.user?.user_type =='V'">
            <span>My Vcard</span>
              <router-link class="link-secondary" :to="{ name: 'NewVcard' }" aria-label="Add a new Vcard" @click="clickMenuOption">
                <i class="bi bi-xs bi-plus-circle"></i>
              </router-link>
          </h6>
          <ul class="nav flex-column mb-2">
            <li class="nav-item" v-show="userStore.user?.user_type =='V'">
              <router-link class="nav-link" :class="{ active: $route.name == 'Vcard' && $route.params.phone_number == userStore.userId }" 
                            :to="{ name: 'Vcard', params: { phone_number: userStore.userId } }" aria-label="Vcard Details" @click="clickMenuOption">
                <i class="bi bi-credit-card-2-front"></i>
                Details
              </router-link>
            </li>
            <li class="nav-item" v-if="userStore.user">
              <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'Transactions'}" :to="{ name: 'Transactions'}" @click="clickMenuOption">
                    <i class="bi bi-wallet"></i>
                    Transactions
                  </router-link>
                </a>
            </li>
            <li class="nav-item" v-if="userStore.user?.user_type =='V'">
              <a class="dropdown-item" href="#">
                  <router-link class="nav-link" :class="{active: $route.name === 'VcardConfirmation' && $route.params.phone_number == userStore.userId}" 
                  :to="{ name: 'VcardConfirmation', params: { phone_number: userStore.userId }}" @click="clickMenuOption">
                    <i class="bi bi-trash"></i>
                    Delete
                  </router-link>
                </a>
            </li>
          </ul>

          <div class="d-block d-md-none">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted" v-if="userStore.user">
              <span>User {{ userStore.user.name }}</span>
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item" v-show="!userStore.user">
                <router-link class="nav-link" :class="{active: $route.name === 'NewVcard'}" :to="{ name: 'NewVcard' }" aria-label="Add a new Vcard" @click="clickMenuOption">
                  <i class="bi bi-person-check-fill"></i>
                  Register
                </router-link>
              </li>
              <li class="nav-item" v-show="!userStore.user">
                <router-link class="nav-link" :class="{active: $route.name === 'Login'}" :to="{ name: 'Login'}" @click="clickMenuOption">
                  <i class="bi bi-house"></i>
                    Login
                </router-link>
              </li>
              <li class="nav-item dropdown" v-show="userStore.user">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink2" role="button"
                  data-bs-toggle="dropdown" aria-expanded="false">
                  <img :src="userStore.userPhotoUrl" class="rounded-circle z-depth-0 avatar-img" alt="avatar image">
                  <span class="avatar-text">{{ userStore.userName }}</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
                  <li>
                    <router-link class="dropdown-item"
                            :class="{ active: $route.name == 'User' && $route.params.id == userStore.userId }" 
                            :to="{ name: 'User', params: { id: userStore.userId } }" @click="clickMenuOption">
                      <i class="bi bi-person-square"></i>
                      Profile
                    </router-link>
                  </li>
                  <li>
                    <router-link class="dropdown-item" :class="{active: $route.name === 'ChangePassword'}" :to="{ name: 'ChangePassword'}" @click="clickMenuOption">
                    <i class="bi bi-key"></i>
                    Change Password
                  </router-link>
                  </li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" @click.prevent="logout">
                      <i class="bi bi-arrow-right"></i>Logout
                    </a></li>
                </ul>
              </li>
            </ul>
          </div>

        </div>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<style>
@import "./assets/dashboard.css";

.avatar-img {
  margin: -1.2rem 0.8rem -2rem 0.8rem;
  width: 3.3rem;
  height: 3.3rem;
}

.avatar-text {
  line-height: 2.2rem;
  margin: 1rem 0.5rem -2rem 0;
  padding-top: 1rem;
}

.dropdown-item {
  font-size: 0.875rem;
}

.btn:focus {
  outline: none;
  box-shadow: none;
}

#sidebarMenu {
  overflow-y: auto;
}
</style>
