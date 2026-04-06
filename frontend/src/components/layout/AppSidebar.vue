<template>
  <aside
    class="fixed top-0 left-0 h-full z-40 flex flex-col transition-all duration-300 border-r border-[var(--color-surface-200)]"
    :class="[
      collapsed ? 'w-[var(--sidebar-collapsed-width)]' : 'w-[var(--sidebar-width)]',
      'bg-[var(--color-surface-50)]'
    ]"
  >
    <!-- Logo -->
    <div class="flex items-center h-[var(--header-height)] px-4 border-b border-[var(--color-surface-200)]">
      <div class="flex items-center gap-3 overflow-hidden">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0">
          <i class="pi pi-chart-line text-white text-lg" />
        </div>
        <transition name="fade">
          <span v-if="!collapsed" class="text-lg font-bold text-[var(--color-surface-900)] whitespace-nowrap">
            FinTrack
          </span>
        </transition>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 px-3 overflow-y-auto">
      <ul class="space-y-1">
        <li v-for="item in navItems" :key="item.route">
          <router-link
            :to="{ name: item.route }"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group"
            :class="[
              isActive(item.route)
                ? 'bg-primary-500/15 text-primary-400'
                : 'text-[var(--color-surface-600)] hover:bg-[var(--color-surface-200)] hover:text-[var(--color-surface-900)]'
            ]"
            :title="collapsed ? item.label : ''"
          >
            <i
              :class="item.icon"
              class="text-lg w-5 text-center flex-shrink-0"
            />
            <transition name="fade">
              <span v-if="!collapsed" class="text-sm font-medium whitespace-nowrap">
                {{ item.label }}
              </span>
            </transition>
          </router-link>
        </li>
      </ul>

      <!-- Admin section -->
      <div v-if="auth.isAdmin" class="mt-6 pt-4 border-t border-[var(--color-surface-200)]">
        <p v-if="!collapsed" class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-[var(--color-surface-500)]">
          Admin
        </p>
        <router-link
          :to="{ name: 'admin' }"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200"
          :class="[
            isActive('admin')
              ? 'bg-primary-500/15 text-primary-400'
              : 'text-[var(--color-surface-600)] hover:bg-[var(--color-surface-200)] hover:text-[var(--color-surface-900)]'
          ]"
        >
          <i class="pi pi-shield text-lg w-5 text-center" />
          <span v-if="!collapsed" class="text-sm font-medium">Admin Panel</span>
        </router-link>
      </div>
    </nav>

    <!-- Collapse toggle -->
    <div class="px-3 py-3 border-t border-[var(--color-surface-200)]">
      <button
        @click="$emit('toggle')"
        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-[var(--color-surface-500)] hover:bg-[var(--color-surface-200)] hover:text-[var(--color-surface-900)] transition-all"
      >
        <i :class="collapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'" />
        <span v-if="!collapsed" class="text-sm">Collapse</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({
  collapsed: Boolean,
})

defineEmits(['toggle'])

const route = useRoute()
const auth = useAuthStore()

const navItems = [
  { label: 'Dashboard',    route: 'dashboard',    icon: 'pi pi-objects-column' },
  { label: 'Transactions', route: 'transactions', icon: 'pi pi-arrow-right-arrow-left' },
  { label: 'Categories',   route: 'categories',   icon: 'pi pi-tags' },
  { label: 'Budgets',      route: 'budgets',      icon: 'pi pi-chart-pie' },
  { label: 'Goals',        route: 'goals',        icon: 'pi pi-flag' },
  { label: 'Recurring',    route: 'recurring',    icon: 'pi pi-sync' },
  { label: 'Net Worth',    route: 'net-worth',    icon: 'pi pi-wallet' },
  { label: 'Reports',      route: 'reports',      icon: 'pi pi-chart-bar' },
  { label: 'Settings',     route: 'settings',     icon: 'pi pi-cog' },
]

function isActive(routeName) {
  return route.name === routeName
}
</script>
