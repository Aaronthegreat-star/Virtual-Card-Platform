//import './assets/main.css'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-icons/font/bootstrap-icons.css"
import "bootstrap"
import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

import FieldErrorMessage from './components/global/FieldErrorMessage.vue'
import ConfirmationDialog from './components/global/ConfirmationDialog.vue'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import axios from 'axios'  
import { io } from "socket.io-client"

import App from './App.vue'
import router from './router'

const app = createApp(App)

const serverBaseUrl = 'http://server-api.test'
app.provide('serverBaseUrl', serverBaseUrl) 
axios.defaults.baseURL = serverBaseUrl + '/api'
axios.defaults.headers.common['Content-type'] = 'application/json'

app.provide('socket', io("http://localhost:8080"))

app.use(Toast, {
    position: "top-center",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: true,
    hideProgressBar: true,
    closeButton: "button",
    icon: true,
    rtl: false
})

app.use(createPinia())
app.use(router)

app.component('FieldErrorMessage', FieldErrorMessage)
app.component('ConfirmationDialog', ConfirmationDialog)

app.mount('#app')
