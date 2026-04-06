<template>
  <div class="glass-card p-5">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Savings Goals</h3>

    <div v-if="goals.length === 0" class="text-center py-6">
      <i class="pi pi-flag text-3xl text-[var(--color-surface-400)] mb-2" />
      <p class="text-sm text-[var(--color-surface-500)]">No savings goals yet</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="g in goals" :key="g.id">
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-sm text-[var(--color-surface-300)]">{{ g.name }}</span>
          <span class="text-xs font-medium" :class="pct(g) >= 100 ? 'text-[var(--color-income)]' : 'text-[var(--color-surface-500)]'">
            {{ pct(g).toFixed(0) }}%
          </span>
        </div>
        <div class="w-full h-2 rounded-full bg-[var(--color-surface-700)] overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :style="{ width: Math.min(pct(g), 100) + '%' }"
            :class="pct(g) >= 100 ? 'bg-[var(--color-income)]' : 'bg-[var(--color-primary-500)]'"
          />
        </div>
        <p class="text-[10px] text-[var(--color-surface-500)] mt-0.5">
          {{ formatNum(g.current_amount) }} of {{ formatNum(g.target_amount) }}
          <span v-if="g.target_date"> &middot; by {{ g.target_date }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ goals: Array })

function formatNum(v) {
  return parseFloat(v || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })
}

function pct(g) {
  const target = parseFloat(g.target_amount) || 1
  const current = parseFloat(g.current_amount) || 0
  return (current / target) * 100
}
</script>
