<template>
  <span
    v-if="value != null && value !== 0"
    class="inline-flex items-center gap-0.5 text-xs font-medium"
    :class="isPositive ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'"
  >
    <i :class="isPositive ? 'pi pi-arrow-up' : 'pi pi-arrow-down'" class="text-[10px]" />
    {{ Math.abs(value).toFixed(1) }}{{ suffix || '%' }}
  </span>
  <span v-else class="text-xs text-[var(--color-surface-500)]">—</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: Number,
  invert: Boolean,
  suffix: String,
})

const isPositive = computed(() => {
  if (props.invert) return (props.value || 0) < 0
  return (props.value || 0) > 0
})
</script>
