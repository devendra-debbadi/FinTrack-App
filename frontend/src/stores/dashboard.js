import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import dashboardService from '@/services/dashboardService'

export const useDashboardStore = defineStore('dashboard', () => {
  const loading = ref(false)
  const period = ref('month')

  const kpis = ref(null)
  const monthlyTrend = ref([])
  const categoryBreakdown = reactive({ expense: [], income: [] })
  const dailySpending = ref([])
  const budgetStatus = ref([])
  const savingsProgress = ref([])
  const recentTransactions = ref([])
  const insights = ref([])

  async function fetchDashboard() {
    loading.value = true
    try {
      const { data } = await dashboardService.getAll(period.value)
      if (data.status === 'success') {
        const d = data.data
        kpis.value = d.kpis
        monthlyTrend.value = d.monthly_trend || []
        categoryBreakdown.expense = d.category_breakdown?.expense || []
        categoryBreakdown.income = d.category_breakdown?.income || []
        dailySpending.value = d.daily_spending || []
        budgetStatus.value = d.budget_status || []
        savingsProgress.value = d.savings_progress || []
        recentTransactions.value = d.recent_transactions || []
        insights.value = d.insights || []
      }
    } finally {
      loading.value = false
    }
  }

  function setPeriod(p) {
    period.value = p
    fetchDashboard()
  }

  return {
    loading,
    period,
    kpis,
    monthlyTrend,
    categoryBreakdown,
    dailySpending,
    budgetStatus,
    savingsProgress,
    recentTransactions,
    insights,
    fetchDashboard,
    setPeriod,
  }
})
