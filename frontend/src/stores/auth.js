import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService'
import { useSettingsStore } from './settings'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const accessToken = ref(localStorage.getItem('access_token') || null)
  const profiles = ref([])
  const activeProfileId = ref(parseInt(localStorage.getItem('active_profile_id')) || null)

  const isAuthenticated = computed(() => !!accessToken.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const currentUser = computed(() => user.value)

  const activeProfile = computed(() => {
    if (activeProfileId.value) {
      return profiles.value.find(p => p.id === activeProfileId.value)
    }
    return profiles.value.find(p => p.is_default) || profiles.value[0]
  })

  async function login(email, password) {
    const { data } = await authService.login(email, password)

    if (data.status === 'success') {
      setAuth(data.data)
      await fetchUserData()
    }

    return data
  }

  async function register(name, email, password) {
    const { data } = await authService.register(name, email, password)

    if (data.status === 'success') {
      setAuth(data.data)
      await fetchUserData()
    }

    return data
  }

  async function fetchUserData() {
    try {
      const { data } = await authService.me()
      if (data.status === 'success') {
        user.value = data.data.user
        profiles.value = data.data.profiles || []
        localStorage.setItem('user', JSON.stringify(data.data.user))

        // Set active profile
        if (!activeProfileId.value && profiles.value.length > 0) {
          const defaultProfile = profiles.value.find(p => p.is_default) || profiles.value[0]
          setActiveProfile(defaultProfile.id)
        }

        // Load settings
        const settingsStore = useSettingsStore()
        if (data.data.settings) {
          settingsStore.setSettings(data.data.settings)
        }
      }
    } catch (e) {
      // Token might be invalid
      if (e.response?.status === 401) {
        logout()
      }
    }
  }

  function setAuth(authData) {
    user.value = authData.user
    accessToken.value = authData.access_token
    localStorage.setItem('access_token', authData.access_token)
    localStorage.setItem('refresh_token', authData.refresh_token)
    localStorage.setItem('user', JSON.stringify(authData.user))
  }

  function setUserData(updatedFields) {
    user.value = { ...user.value, ...updatedFields }
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  function setActiveProfile(profileId) {
    activeProfileId.value = profileId
    localStorage.setItem('active_profile_id', profileId)
  }

  async function logout() {
    try {
      await authService.logout()
    } catch {
      // Ignore errors during logout
    }

    user.value = null
    accessToken.value = null
    profiles.value = []
    activeProfileId.value = null
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
    localStorage.removeItem('user')
    localStorage.removeItem('active_profile_id')
  }

  return {
    user,
    accessToken,
    profiles,
    activeProfileId,
    activeProfile,
    isAuthenticated,
    isAdmin,
    currentUser,
    login,
    register,
    logout,
    fetchUserData,
    setUserData,
    setActiveProfile,
  }
})
