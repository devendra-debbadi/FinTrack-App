<template>
  <div class="glass-card p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-[var(--color-surface-700)]">Spending by Category</h3>
      <div class="flex gap-1">
        <button
          v-for="opt in ['expense', 'income']"
          :key="opt"
          @click="activeType = opt"
          class="text-xs px-2.5 py-1 rounded-lg capitalize transition-colors"
          :class="activeType === opt
            ? 'bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)] font-medium'
            : 'text-[var(--color-surface-500)] hover:text-[var(--color-surface-300)]'"
        >
          {{ opt }}
        </button>
      </div>
    </div>
    <v-chart :option="chartOption" autoresize style="height: 280px" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { PieChart } from 'echarts/charts'
import { TooltipComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

use([PieChart, TooltipComponent, LegendComponent, CanvasRenderer])

const props = defineProps({ data: Object })
const activeType = ref('expense')

const palette = [
  '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
  '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4',
  '#3b82f6', '#d946ef', '#14b8a6', '#0ea5e9', '#ef4444',
]

const chartOption = computed(() => {
  const items = (activeType.value === 'income' ? props.data?.income : props.data?.expense) || []
  const seriesData = items.map((item, i) => ({
    name: item.category_name,
    value: parseFloat(item.total),
    itemStyle: { color: palette[i % palette.length] },
  }))

  return {
    tooltip: {
      trigger: 'item',
      backgroundColor: 'rgba(15, 15, 25, 0.9)',
      borderColor: 'rgba(99, 102, 241, 0.3)',
      textStyle: { color: '#e2e8f0', fontSize: 12 },
      formatter: (p) => `${p.name}<br/><b>${p.value.toLocaleString('en-US', { minimumFractionDigits: 2 })}</b> (${p.percent}%)`,
    },
    legend: {
      orient: 'vertical',
      right: 0,
      top: 'center',
      textStyle: { color: '#94a3b8', fontSize: 11 },
      icon: 'circle',
      itemWidth: 8,
      itemHeight: 8,
    },
    series: [
      {
        type: 'pie',
        radius: ['50%', '75%'],
        center: ['35%', '50%'],
        avoidLabelOverlap: true,
        label: { show: false },
        emphasis: {
          label: { show: true, fontSize: 13, fontWeight: 'bold', color: '#e2e8f0' },
          itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0, 0, 0, 0.3)' },
        },
        data: seriesData,
      },
    ],
  }
})
</script>
