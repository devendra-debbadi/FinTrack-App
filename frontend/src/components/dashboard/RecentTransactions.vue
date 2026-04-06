<template>
  <div class="glass-card p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-[var(--color-surface-700)]">Recent Transactions</h3>
      <router-link to="/transactions" class="text-xs text-[var(--color-primary-400)] hover:underline">
        View all
      </router-link>
    </div>

    <div v-if="transactions.length === 0" class="text-center py-6">
      <i class="pi pi-receipt text-3xl text-[var(--color-surface-400)] mb-2" />
      <p class="text-sm text-[var(--color-surface-500)]">No recent transactions</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="txn in transactions"
        :key="txn.id"
        class="flex items-center gap-3"
      >
        <div
          class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
          :style="{ backgroundColor: (txn.category_color || '#6366f1') + '20', color: txn.category_color || '#6366f1' }"
        >
          <i :class="txn.category_icon || 'pi pi-tag'" class="text-sm" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-[var(--color-surface-300)] truncate">{{ txn.description || txn.category_name || 'Transaction' }}</p>
          <p class="text-[10px] text-[var(--color-surface-500)]">{{ formatDate(txn.transaction_date) }}</p>
        </div>
        <span
          class="text-sm font-semibold whitespace-nowrap"
          :class="txn.type === 'income' ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'"
        >
          {{ txn.type === 'income' ? '+' : '-' }}{{ formatAmount(txn.amount, txn.currency) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({ transactions: Array })
const { formatAmount } = useCurrency()

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}
</script>
