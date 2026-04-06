<template>
  <div class="glass-card p-5">
    <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Daily Spending (Last 12 Months)</h3>
    <v-chart :option="chartOption" autoresize style="height: 200px" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { HeatmapChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, VisualMapComponent, CalendarComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

use([HeatmapChart, GridComponent, TooltipComponent, VisualMapComponent, CalendarComponent, CanvasRenderer])

const props = defineProps({ data: Array })

const chartOption = computed(() => {
  const spending = props.data || []
  const heatData = spending.map(d => [d.date, parseFloat(d.amount)])
  const maxVal = Math.max(...heatData.map(d => d[1]), 1)

  // Calculate date range: 12 months ago to today
  const today = new Date()
  const yearAgo = new Date(today)
  yearAgo.setFullYear(yearAgo.getFullYear() - 1)
  yearAgo.setDate(yearAgo.getDate() + 1)

  const rangeStart = yearAgo.toISOString().slice(0, 10)
  const rangeEnd = today.toISOString().slice(0, 10)

  return {
    tooltip: {
      backgroundColor: 'rgba(15, 15, 25, 0.9)',
      borderColor: 'rgba(99, 102, 241, 0.3)',
      textStyle: { color: '#e2e8f0', fontSize: 12 },
      formatter: (p) => {
        const [date, amount] = p.data
        return `${date}<br/>Spent: <b>${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}</b>`
      },
    },
    visualMap: {
      min: 0,
      max: maxVal,
      show: false,
      inRange: {
        color: ['#1e1b4b', '#3730a3', '#6366f1', '#a855f7', '#f43f5e'],
      },
    },
    calendar: {
      range: [rangeStart, rangeEnd],
      cellSize: [14, 14],
      top: 30,
      left: 40,
      right: 10,
      itemStyle: {
        borderWidth: 2,
        borderColor: 'rgba(30, 41, 59, 0.5)',
        color: '#0f0f19',
      },
      yearLabel: { show: false },
      monthLabel: { color: '#94a3b8', fontSize: 10 },
      dayLabel: { color: '#64748b', fontSize: 9, firstDay: 1 },
      splitLine: { show: false },
    },
    series: [
      {
        type: 'heatmap',
        coordinateSystem: 'calendar',
        data: heatData,
      },
    ],
  }
})
</script>
