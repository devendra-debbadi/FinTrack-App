<template>
  <header class="h-[var(--header-height)] flex items-center justify-between px-6 border-b border-[var(--color-surface-200)] bg-[var(--color-surface-50)]">
    <!-- Left: mobile menu + page title -->
    <div class="flex items-center gap-4">
      <button
        @click="$emit('toggleSidebar')"
        class="lg:hidden p-2 rounded-lg text-[var(--color-surface-600)] hover:bg-[var(--color-surface-200)]"
      >
        <i class="pi pi-bars text-lg" />
      </button>
      <h1 class="text-lg font-semibold text-[var(--color-surface-900)]">
        {{ pageTitle }}
      </h1>
    </div>

    <!-- Right: profile switcher, theme, user menu -->
    <div class="flex items-center gap-3">
      <!-- Profile Switcher -->
      <Select
        v-if="auth.profiles.length > 1"
        v-model="selectedProfileId"
        :options="auth.profiles"
        optionLabel="name"
        optionValue="id"
        class="w-40"
        size="small"
        @change="onProfileChange"
      />

      <!-- Theme Toggle -->
      <button
        @click="toggleTheme()"
        class="p-2 rounded-lg text-[var(--color-surface-600)] hover:bg-[var(--color-surface-200)] transition-colors"
        :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <i :class="theme === 'dark' ? 'pi pi-sun' : 'pi pi-moon'" class="text-lg" />
      </button>

      <!-- User Menu -->
      <div class="relative" ref="menuRef">
        <button
          @click="showMenu = !showMenu"
          class="flex items-center gap-2 p-1.5 pr-3 rounded-xl hover:bg-[var(--color-surface-200)] transition-colors"
        >
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
            <span class="text-white text-sm font-semibold">
              {{ userInitial }}
            </span>
          </div>
          <span class="hidden sm:block text-sm font-medium text-[var(--color-surface-800)]">
            {{ auth.currentUser?.name }}
          </span>
          <i class="pi pi-chevron-down text-xs text-[var(--color-surface-500)]" />
        </button>

        <!-- Dropdown -->
        <transition name="fade">
          <div
            v-if="showMenu"
            class="absolute right-0 top-full mt-2 w-48 py-1 rounded-xl bg-[var(--color-surface-100)] border border-[var(--color-surface-200)] shadow-lg z-50"
          >
            <router-link
              :to="{ name: 'settings' }"
              class="flex items-center gap-2 px-4 py-2 text-sm text-[var(--color-surface-700)] hover:bg-[var(--color-surface-200)]"
              @click="showMenu = false"
            >
              <i class="pi pi-cog" />
              Settings
            </router-link>
            <router-link
              :to="{ name: 'profiles' }"
              class="flex items-center gap-2 px-4 py-2 text-sm text-[var(--color-surface-700)] hover:bg-[var(--color-surface-200)]"
              @click="showMenu = false"
            >
              <i class="pi pi-users" />
              Profiles
            </router-link>
            <hr class="my-1 border-[var(--color-surface-200)]" />
            <button
              @click="handleLogout"
              class="w-full flex items-center gap-2 px-4 py-2 text-sm text-[var(--color-expense)] hover:bg-[var(--color-surface-200)]"
            >
              <i class="pi pi-sign-out" />
              Logout
            </button>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/composables/useTheme'
import Select from 'primevue/select'

defineEmits(['toggleSidebar'])

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { theme, toggleTheme } = useTheme()

const showMenu = ref(false)
const menuRef = ref(null)

const selectedProfileId = ref(auth.activeProfileId)

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard',
    transactions: 'Transactions',
    categories: 'Categories',
    budgets: 'Budgets',
    goals: 'Savings Goals',
    reports: 'Reports',
    settings: 'Settings',
    profiles: 'Profiles',
    admin: 'Admin Panel',
  }
  return titles[route.name] || 'FinTrack'
})

const userInitial = computed(() => {
  return auth.currentUser?.name?.charAt(0)?.toUpperCase() || '?'
})

function onProfileChange() {
  auth.setActiveProfile(selectedProfileId.value)
  window.location.reload()
}

async function handleLogout() {
  showMenu.value = false
  await auth.logout()
  router.push({ name: 'login' })
}

// Close menu on outside click
function handleClickOutside(e) {
  if (menuRef.value && !menuRef.value.contains(e.target)) {
    showMenu.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>
