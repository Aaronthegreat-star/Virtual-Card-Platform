import axios from 'axios'
import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from "vue-toastification" //comentario não sabemos se é preciso DG
import avatarNoneUrl from '@/assets/avatar-none.png'

export const useUserStore = defineStore('user', () => {

    const socket = inject("socket")
    const toast = useToast()  //comentario não sabemos se é preciso DG
    const serverBaseUrl = inject('serverBaseUrl') 

    const user = ref(null)

    const userType = computed(() => user.value?.user_type ?? 'Anonymous')

    const userName = computed(() => user.value?.name ?? 'Anonymous')

    const userId = computed(() => user.value?.id ?? -1)

    const userPhotoUrl = computed(() =>
        user.value?.photo_url
            ? serverBaseUrl + '/storage/fotos/' + user.value.photo_url
            : avatarNoneUrl)

    async function loadUser() {
        try {
            const response = await axios.get('users/me')
            user.value = response.data.data
        } catch (error) {
            clearUser()
            throw error
        }
    }

    function clearUser() {
        delete axios.defaults.headers.common.Authorization
        sessionStorage.removeItem('token')
        user.value = null
    }

    async function login(credentials) {
        try {
            const response = await axios.post('login', credentials)
            axios.defaults.headers.common.Authorization = "Bearer " + response.data.access_token
            sessionStorage.setItem('token', response.data.access_token)
            await loadUser()
            socket.emit('loggedIn', user.value)   //comentario não sabemos se é preciso DG
            return true
        } catch (error) {
            clearUser()
            return false
        }
    }

    async function logout() {
        try {
            await axios.post('logout')
            socket.emit('loggedOut', user.value)  //comentario não sabemos se é preciso DG
            clearUser()
            return true
        } catch (error) {
            return false
        }
    }

    async function changePassword(credentials) {

        if (userId.value < 0) {
            throw 'Anonymous users cannot change the password!'
        }

        if (userType.value == 'A') {
            try {
                await axios.patch(`admins/${user.value.id}/password`, credentials)
                return true
            } catch (error) {
                throw error
            }
        } else {
            try {
                await axios.patch(`vcards/${user.value.id}/password`, credentials)
                return true
            } catch (error) {
                throw error
            }
        }
    }

    async function changeConfirmationCode(credentials) {

        if (userId.value < 0) {
            throw 'Anonymous users cannot change the confirmation code!'
        }

        if (userType.value == 'A') {
            throw 'Administrators do not have a confirmation code!'
        }

        try {
            await axios.patch(`vcards/${user.value.id}/confirmationCode`, credentials)
            return true
        } catch (error) {
            throw error
        }
    }

    async function restoreToken() {
        let storedToken = sessionStorage.getItem('token')
        if (storedToken) {
            axios.defaults.headers.common.Authorization = "Bearer " + storedToken
            await loadUser()
            socket.emit('loggedIn', user.value)  //comentario não sabemos se é preciso DG
            return true
        }
        clearUser()
        return false
    }

    return { user, userId, userName, userPhotoUrl, loadUser, clearUser, login, logout, restoreToken, changePassword, changeConfirmationCode }
})