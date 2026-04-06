<template>
  <div class="glass-card p-5">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">
      <i class="pi pi-sparkles mr-1 text-[var(--color-primary-400)]" />
      Smart Insights
    </h3>

    <div v-if="insights.length === 0" class="text-center py-6">
      <i class="pi pi-info-circle text-3xl text-[var(--color-surface-400)] mb-2" />
      <p class="text-sm text-[var(--color-surface-500)]">Add more transactions to unlock insights</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="(insight, i) in insights"
        :key="i"
        class="flex items-start gap-3 p-3 rounded-xl"
        :class="bgClass(insight.type)"
      >
        <div
          class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
          :class="iconBgClass(insight.type)"
        >
          <i :class="'pi ' + insight.icon" class="text-sm" :style="{ color: iconColor(insight.type) }" />
        </div>
        <p class="text-sm text-[var(--color-surface-300)] leading-relaxed">{{ insight.message }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ insights: Array })

function bgClass(type) {
  if (type === 'warning') return 'bg-[#f59e0b]/5'
  if (type === 'success') return 'bg-[var(--color-income)]/5'
  return 'bg-[var(--color-primary-500)]/5'
}

function iconBgClass(type) {
  if (type === 'warning') return 'bg-[#f59e0b]/15'
  if (type === 'success') return 'bg-[var(--color-income)]/15'
  return 'bg-[var(--color-primary-500)]/15'
}

function iconColor(type) {
  if (type === 'warning') return '#f59e0b'
  if (type === 'success') return '#10b981'
  return '#6366f1'
}
</script>
