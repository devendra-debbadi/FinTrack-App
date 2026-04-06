<template>
  <div>
    <!-- Header with period selector -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Dashboard</h2>
      <div class="flex gap-1 bg-[var(--color-surface-800)] rounded-xl p-1">
        <button
          v-for="opt in periodOptions"
          :key="opt.value"
          @click="store.setPeriod(opt.value)"
          class="text-xs px-3 py-1.5 rounded-lg transition-colors"
          :class="store.period === opt.value
            ? 'bg-[var(--color-primary-500)] text-white font-medium'
            : 'text-[var(--color-surface-400)] hover:text-[var(--color-surface-200)]'"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex justify-center py-20">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <template v-else>
      <!-- KPI Cards -->
      <KpiCards :kpis="store.kpis" class="mb-6" />

      <!-- Row 1: Trend + Category Breakdown -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <div class="lg:col-span-2">
          <SpendingTrendChart :data="store.monthlyTrend" />
        </div>
        <CategoryBreakdownChart :data="store.categoryBreakdown" />
      </div>

      <!-- Row 2: Bar Chart + Heatmap -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <IncomeExpenseBarChart :data="store.monthlyTrend" />
        <SpendingHeatmap :data="store.dailySpending" />
      </div>

      <!-- Row 3: Budget + Savings + Insights -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <BudgetProgress :budgets="store.budgetStatus" />
        <SavingsProgress :goals="store.savingsProgress" />
        <InsightsPanel :insights="store.insights" />
      </div>

      <!-- Row 4: Recent Transactions -->
      <RecentTransactions :transactions="store.recentTransactions" />
    </template>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import ProgressSpinner from 'primevue/progressspinner'
import KpiCards from '@/components/dashboard/KpiCards.vue'
import SpendingTrendChart from '@/components/dashboard/SpendingTrendChart.vue'
import CategoryBreakdownChart from '@/components/dashboard/CategoryBreakdownChart.vue'
import IncomeExpenseBarChart from '@/components/dashboard/IncomeExpenseBarChart.vue'
import SpendingHeatmap from '@/components/dashboard/SpendingHeatmap.vue'
import BudgetProgress from '@/components/dashboard/BudgetProgress.vue'
import SavingsProgress from '@/components/dashboard/SavingsProgress.vue'
import InsightsPanel from '@/components/dashboard/InsightsPanel.vue'
import RecentTransactions from '@/components/dashboard/RecentTransactions.vue'

const store = useDashboardStore()

const periodOptions = [
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' },
  { label: 'Quarter', value: 'quarter' },
  { label: 'Year', value: 'year' },
]

onMounted(() => store.fetchDashboard())
</script>
