<template>
  <div class="min-h-screen flex items-center justify-center bg-[var(--color-surface-0)] px-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 mb-4">
          <i class="pi pi-chart-line text-white text-3xl" />
        </div>
        <h1 class="text-2xl font-bold text-[var(--color-surface-900)]">Create Account</h1>
        <p class="text-[var(--color-surface-500)] mt-1">Start tracking your finances with FinTrack</p>
      </div>

      <!-- Register form -->
      <div class="glass-card p-8">
        <form @submit.prevent="handleRegister">
          <!-- Error -->
          <div v-if="error" class="mb-4 p-3 rounded-lg bg-[var(--color-expense)]/10 border border-[var(--color-expense)]/30 text-[var(--color-expense)] text-sm">
            {{ error }}
          </div>

          <!-- Name -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1.5">Name</label>
            <InputText
              v-model="name"
              placeholder="Your name"
              class="w-full"
              :invalid="!!errors.name"
            />
            <small v-if="errors.name" class="text-[var(--color-expense)] text-xs mt-1">{{ errors.name }}</small>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1.5">Email</label>
            <InputText
              v-model="email"
              type="email"
              placeholder="you@example.com"
              class="w-full"
              :invalid="!!errors.email"
            />
            <small v-if="errors.email" class="text-[var(--color-expense)] text-xs mt-1">{{ errors.email }}</small>
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1.5">Password</label>
            <Password
              v-model="password"
              placeholder="Min 8 characters"
              class="w-full"
              toggleMask
              inputClass="w-full"
              :invalid="!!errors.password"
            />
            <small v-if="errors.password" class="text-[var(--color-expense)] text-xs mt-1">{{ errors.password }}</small>
          </div>

          <!-- Submit -->
          <Button
            type="submit"
            label="Create Account"
            icon="pi pi-user-plus"
            class="w-full"
            :loading="loading"
            severity="primary"
          />
        </form>

        <p class="text-center mt-6 text-sm text-[var(--color-surface-500)]">
          Already have an account?
          <router-link :to="{ name: 'login' }" class="text-primary-400 hover:text-primary-300 font-medium">
            Sign in
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const errors = reactive({ name: '', email: '', password: '' })

async function handleRegister() {
  error.value = ''
  errors.name = ''
  errors.email = ''
  errors.password = ''

  if (!name.value) { errors.name = 'Name is required'; return }
  if (!email.value) { errors.email = 'Email is required'; return }
  if (!password.value || password.value.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    return
  }

  loading.value = true

  try {
    const data = await auth.register(name.value, email.value, password.value)

    if (data.status === 'success') {
      router.push({ name: 'dashboard' })
    } else {
      error.value = data.message || 'Registration failed'
    }
  } catch (e) {
    if (e.response?.data?.message) {
      error.value = e.response.data.message
    } else {
      error.value = 'Unable to connect to server'
    }
  } finally {
    loading.value = false
  }
}
</script>
