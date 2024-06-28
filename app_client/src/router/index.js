import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Dashboard from "../components/Dashboard.vue"
import Users from "../components/users/Users.vue"
import User from "../components/users/User.vue"
import Login from "../components/auth/Login.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import ChangeConfirmationCode from "../components/auth/ChangeConfirmationCode.vue"
import Vcards from '../components/vcards/vcards.vue'
import Vcard from '../components/vcards/vcard.vue'
import VcardConfirmation from '../components/vcards/VcardConfirmation.vue'
import Transactions from '../components/transactions/transactions.vue'
import Transaction from '../components/transactions/transaction.vue'
import Categories from '../components/categories/categories.vue'
import Category from '../components/categories/category.vue'
import { useUserStore } from "../stores/user.js"
import axios from 'axios'
//import Transactions from "../components/transactions/Transactions.vue"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    // {
    //   path: '/about',
    //   name: 'about',
    //   // route level code-splitting
    //   // this generates a separate chunk (About.[hash].js) for this route
    //   // which is lazy-loaded when the route is visited.
    //   component: () => import('../views/AboutView.vue')
    // },
    {
      path: '/login',
      name: 'Login',
      component: Login
    },
    {
      path: '/password',
      name: 'ChangePassword',
      component: ChangePassword 
    },
    {
      path: '/confirmationCode',
      name: 'ChangeConfirmationCode',
      component: ChangeConfirmationCode 
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: Dashboard 
    },
    {
      path: '/users',
      name: 'Users',
      component: Users 
    },
    {
      path: '/users/:id',
      name: 'User',
      component: User,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/users/new',
      name: 'NewUser',
      component: User,
      props: { id: -1 }
    },
    {
      path: '/vcards',
      name: 'Vcards',
      component: Vcards 
    },
    {
      path: '/vcards/new',
      name: 'NewVcard',
      component: Vcard,
    },
    {
      path: '/vcards/:phone_number',
      name: 'Vcard',
      component: Vcard,
      props: route => ({ phone_number: parseInt(route.params.phone_number) })
    },
    {
      path: '/vcards/:phone_number/delete',
      name: 'VcardConfirmation',
      component: VcardConfirmation,
      props: route => ({ phone_number: parseInt(route.params.phone_number) })
    },
    {
      path: '/transactions',
      name: 'Transactions',
      component: Transactions
    },
    {
      path: '/transactions/new',
      name: 'NewTransaction',
      component: Transaction,
      props: { id: -1 }
    },
    {
      path: '/transactions/request',
      name: 'RequestTransaction',
      component: Transaction,
      props: { id: -2 }
    },
    {
      path: '/transactions/:id',
      name: 'Transaction',
      component: Transaction,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/categories',
      name: 'Categories',
      component: Categories
    },
    {
      path: '/categories/:id',
      name: 'Category',
      component: Category,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/categories/new',
      name: 'NewCategory',
      component: Category,
      props: { id: -1 }
    },
  ]
})

let handlingFirstRoute = true

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  if (handlingFirstRoute) {
      handlingFirstRoute = false
      await userStore.restoreToken()
  }
  if (to.name == 'NewVcard') {
    next()
    return
  }
  if ((to.name == 'Login' || (to.name == 'home'))){
    if (userStore.user){
      next({name: 'Dashboard'})
    }
    next()
    return
  }
  if (!userStore.user) {
    next({ name: 'Login' })
    return
  }
  if (to.name == 'ChangeConfirmationCode') {
    if (userStore.user.user_type == 'V') {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'Users') {
    if (userStore.user.user_type != 'A') {
      next({ name: 'home' })
      return
    }
  }
  if (to.name == 'User') {
    if ((userStore.user.user_type == 'A') || (userStore.user.id == to.params.id)) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'NewUser') {
    if ((userStore.user.user_type == 'A')) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'Vcards') {
    if (userStore.user.user_type != 'A') {
      next({ name: 'home' })
      return
    }
  }
  if (to.name == 'Vcard') {
    if ((userStore.user.user_type == 'A') || (userStore.user.id == to.params.phone_number)) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'VcardConfirmation') {
    if ((userStore.user.user_type == 'A') || (userStore.user.id == to.params.phone_number)) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
//=============================================VERIFICAR COM STOR=======================================================================================
  if (to.name == 'Transactions') {
    if (userStore.user) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'Transaction') {
    const transactionId = parseInt(to.params.id);

    const response = await axios.get(`http://server-api.test/api/transactions/${transactionId}`);
    const transaction = response.data;
    const phone_number = transaction.data.vcard.phone_number;

    if (userStore.user.id == phone_number) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  // if (to.name == 'Transaction') {
  //   if (userStore.user) {
  //     next()
  //     return
  //   }
  //   next({ name: 'home' })
  //   return
  // }
  if (to.name == 'NewTransaction') {
    if (userStore.user) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'RequestTransaction') {
    if (userStore.user.user_type == 'V') {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'Categories') {
    if (userStore.user) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'NewCategory') {
    if (userStore.user) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  if (to.name == 'Category') {

    if (userStore.user.user_type == 'A'){
      next()
      return
    }

    const categoryId = parseInt(to.params.id);
    const response = await axios.get(`http://server-api.test/api/categories/${categoryId}`);
    const category = response.data;
    const phone_number = category.data.vcard.phone_number;

    if (userStore.user.id == phone_number) {
      next()
      return
    }
    next({ name: 'home' })
    return
  }
  next()
})

export default router
