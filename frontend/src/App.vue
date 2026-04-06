<template>
  <Toast position="top-right" />
  <ConfirmDialog />
  <router-view />
</template>

<script setup>
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'

const auth = useAuthStore()

// Initialize theme
useTheme()

// Re-fetch user data if we have a token
onMounted(async () => {
  if (auth.isAuthenticated) {
    await auth.fetchUserData()
  }
})
</script>
