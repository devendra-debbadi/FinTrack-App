<template>
  <div class="glass-card overflow-hidden">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] px-4 pt-4 pb-2">{{ title }}</h3>
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-[var(--color-surface-700)]">
          <th class="text-left py-2.5 px-4 text-[var(--color-surface-500)] font-medium">Category</th>
          <th class="text-right py-2.5 px-4 text-[var(--color-surface-500)] font-medium">Txns</th>
          <th class="text-right py-2.5 px-4 text-[var(--color-surface-500)] font-medium">Amount</th>
          <th class="text-right py-2.5 px-4 text-[var(--color-surface-500)] font-medium w-24">Share</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="cat in categories"
          :key="cat.category_name"
          class="border-b border-[var(--color-surface-800)]"
        >
          <td class="py-2.5 px-4">
            <div class="flex items-center gap-2">
              <i :class="cat.category_icon" :style="{ color: cat.category_color }" class="text-sm" />
              <span class="text-[var(--color-surface-300)]">{{ cat.category_name }}</span>
            </div>
          </td>
          <td class="py-2.5 px-4 text-right text-[var(--color-surface-500)]">{{ cat.count }}</td>
          <td class="py-2.5 px-4 text-right text-[var(--color-surface-300)] font-medium">{{ fmtAmt(cat.total) }}</td>
          <td class="py-2.5 px-4 text-right">
            <div class="flex items-center gap-2 justify-end">
              <div class="w-12 h-1.5 rounded-full bg-[var(--color-surface-700)] overflow-hidden">
                <div
                  class="h-full rounded-full"
                  :style="{ width: pct(cat) + '%', backgroundColor: cat.category_color || '#6366f1' }"
                />
              </div>
              <span class="text-xs text-[var(--color-surface-500)] w-8 text-right">{{ pct(cat).toFixed(0) }}%</span>
            </div>
          </td>
        </tr>
        <tr v-if="!categories?.length">
          <td colspan="4" class="py-6 text-center text-[var(--color-surface-500)]">No data</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
import { useSettingsStore } from '@/stores/settings'

const props = defineProps({
  title: String,
  categories: Array,
})

const { formatAmount } = useCurrency()
const settingsStore = useSettingsStore()

function fmtAmt(val) {
  return formatAmount(val || 0, settingsStore.currency)
}

const totalAmount = computed(() =>
  (props.categories || []).reduce((sum, c) => sum + parseFloat(c.total || 0), 0)
)

function pct(cat) {
  if (!totalAmount.value) return 0
  return (parseFloat(cat.total || 0) / totalAmount.value) * 100
}
</script>
