<template>
  <div class="glass-card p-5">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Budget Status</h3>

    <div v-if="budgets.length === 0" class="text-center py-6">
      <i class="pi pi-chart-bar text-3xl text-[var(--color-surface-400)] mb-2" />
      <p class="text-sm text-[var(--color-surface-500)]">No budgets set</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="b in budgets" :key="b.id">
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-sm text-[var(--color-surface-300)]">{{ b.name }}</span>
          <span class="text-xs text-[var(--color-surface-500)]">
            {{ formatNum(b.spent) }} / {{ formatNum(b.amount) }}
          </span>
        </div>
        <div class="w-full h-2 rounded-full bg-[var(--color-surface-700)] overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :style="{ width: pct(b) + '%', backgroundColor: barColor(b) }"
          />
        </div>
        <p v-if="pct(b) > 90" class="text-[10px] text-[var(--color-expense)] mt-0.5">
          {{ pct(b) >= 100 ? 'Over budget!' : 'Almost at limit' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ budgets: Array })

function formatNum(v) {
  return parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

function pct(b) {
  const limit = parseFloat(b.amount) || 1
  const spent = parseFloat(b.spent) || 0
  return Math.min((spent / limit) * 100, 100)
}

function barColor(b) {
  const p = pct(b)
  if (p >= 90) return '#f43f5e'
  if (p >= 70) return '#f59e0b'
  return '#6366f1'
}
</script>
