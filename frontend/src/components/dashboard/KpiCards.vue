<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Income -->
    <div class="kpi-card">
      <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-[var(--color-surface-500)]">Income</span>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-[var(--color-income)]/15">
          <i class="pi pi-arrow-down-left text-[var(--color-income)]" />
        </div>
      </div>
      <p class="text-2xl font-bold text-[var(--color-surface-900)]">{{ formatAmount(kpis?.income?.amount) }}</p>
      <div class="flex items-center gap-1 mt-1">
        <ChangeIndicator :value="kpis?.income?.change" />
        <span class="text-xs text-[var(--color-surface-500)]">{{ kpis?.income?.count || 0 }} transactions</span>
      </div>
    </div>

    <!-- Expense -->
    <div class="kpi-card">
      <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-[var(--color-surface-500)]">Expenses</span>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-[var(--color-expense)]/15">
          <i class="pi pi-arrow-up-right text-[var(--color-expense)]" />
        </div>
      </div>
      <p class="text-2xl font-bold text-[var(--color-surface-900)]">{{ formatAmount(kpis?.expense?.amount) }}</p>
      <div class="flex items-center gap-1 mt-1">
        <ChangeIndicator :value="kpis?.expense?.change" invert />
        <span class="text-xs text-[var(--color-surface-500)]">{{ kpis?.expense?.count || 0 }} transactions</span>
      </div>
    </div>

    <!-- Balance -->
    <div class="kpi-card">
      <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-[var(--color-surface-500)]">Net Balance</span>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-[var(--color-primary-500)]/15">
          <i class="pi pi-wallet text-[var(--color-primary-400)]" />
        </div>
      </div>
      <p class="text-2xl font-bold" :class="(kpis?.balance?.amount || 0) >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
        {{ formatAmount(kpis?.balance?.amount) }}
      </p>
      <div class="flex items-center gap-1 mt-1">
        <ChangeIndicator :value="kpis?.balance?.change" />
      </div>
    </div>

    <!-- Savings Rate -->
    <div class="kpi-card">
      <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-[var(--color-surface-500)]">Savings Rate</span>
        <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-[var(--color-primary-500)]/15">
          <i class="pi pi-percentage text-[var(--color-primary-400)]" />
        </div>
      </div>
      <p class="text-2xl font-bold text-[var(--color-surface-900)]">{{ (kpis?.savings_rate?.percentage || 0).toFixed(1) }}%</p>
      <div class="flex items-center gap-1 mt-1">
        <ChangeIndicator :value="kpis?.savings_rate?.change" suffix="pp" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCurrency } from '@/composables/useCurrency'
import { useSettingsStore } from '@/stores/settings'
import ChangeIndicator from './ChangeIndicator.vue'

const props = defineProps({ kpis: Object })
const { formatAmount: fmt } = useCurrency()
const settingsStore = useSettingsStore()

function formatAmount(val) {
  return fmt(val || 0, settingsStore.currency)
}
</script>
