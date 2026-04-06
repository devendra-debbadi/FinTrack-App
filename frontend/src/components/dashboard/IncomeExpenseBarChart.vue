<template>
  <div class="glass-card p-5">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Income vs Expenses</h3>
    <v-chart :option="chartOption" autoresize style="height: 280px" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { BarChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

use([BarChart, GridComponent, TooltipComponent, LegendComponent, CanvasRenderer])

const props = defineProps({ data: Array })

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const chartOption = computed(() => {
  const trend = props.data || []
  const incomeData = months.map((_, i) => {
    const m = trend.find(t => t.month === i + 1)
    return m ? parseFloat(m.income) : 0
  })
  const expenseData = months.map((_, i) => {
    const m = trend.find(t => t.month === i + 1)
    return m ? parseFloat(m.expense) : 0
  })

  return {
    tooltip: {
      trigger: 'axis',
      backgroundColor: 'rgba(15, 15, 25, 0.9)',
      borderColor: 'rgba(99, 102, 241, 0.3)',
      textStyle: { color: '#e2e8f0', fontSize: 12 },
    },
    legend: {
      data: ['Income', 'Expenses'],
      textStyle: { color: '#94a3b8' },
      bottom: 0,
    },
    grid: { top: 10, right: 16, bottom: 36, left: 50, containLabel: false },
    xAxis: {
      type: 'category',
      data: months,
      axisLine: { lineStyle: { color: '#334155' } },
      axisLabel: { color: '#94a3b8', fontSize: 11 },
    },
    yAxis: {
      type: 'value',
      splitLine: { lineStyle: { color: '#1e293b' } },
      axisLabel: { color: '#94a3b8', fontSize: 11 },
    },
    series: [
      {
        name: 'Income',
        type: 'bar',
        data: incomeData,
        barWidth: '35%',
        itemStyle: {
          color: {
            type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: '#10b981' },
              { offset: 1, color: 'rgba(16, 185, 129, 0.4)' },
            ],
          },
          borderRadius: [4, 4, 0, 0],
        },
      },
      {
        name: 'Expenses',
        type: 'bar',
        data: expenseData,
        barWidth: '35%',
        itemStyle: {
          color: {
            type: 'linear', x: 0, y: 0, x2: 0, y2: 1,
            colorStops: [
              { offset: 0, color: '#f43f5e' },
              { offset: 1, color: 'rgba(244, 63, 94, 0.4)' },
            ],
          },
          borderRadius: [4, 4, 0, 0],
        },
      },
    ],
  }
})
</script>
