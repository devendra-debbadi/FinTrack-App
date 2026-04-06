<template>
  <div>
    <h2 class="text-2xl font-bold text-[var(--color-surface-900)] mb-6">Reports</h2>

    <!-- Tabs -->
    <Tabs v-model:value="activeTab" class="mb-4">
      <TabList>
        <Tab value="monthly">Monthly</Tab>
        <Tab value="yearly">Yearly</Tab>
        <Tab value="comparison">Income vs Expense</Tab>
        <Tab value="budget">Budget Performance</Tab>
        <Tab value="category">By Category</Tab>
      </TabList>
    </Tabs>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <!-- Monthly Report -->
    <template v-else-if="activeTab === 'monthly'">
      <!-- Period selector -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-chevron-left" severity="secondary" text @click="monthlyPrev" />
          <span class="text-lg font-semibold text-[var(--color-surface-300)] min-w-[160px] text-center">
            {{ monthNames[monthlyMonth - 1] }} {{ monthlyYear }}
          </span>
          <Button icon="pi pi-chevron-right" severity="secondary" text @click="monthlyNext" />
        </div>
        <Button v-if="monthlyData" icon="pi pi-file-pdf" label="Export PDF" severity="secondary" text size="small"
          @click="exportMonthlyPdf(monthlyData, monthNames[monthlyMonth - 1], monthlyYear)" />
      </div>

      <template v-if="monthlyData">
        <!-- KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Income</p>
            <p class="text-xl font-bold text-[var(--color-income)]">{{ fmtAmt(monthlyData.totals.income) }}</p>
            <p class="text-[10px] text-[var(--color-surface-500)]">{{ monthlyData.totals.income_count }} transactions</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Expenses</p>
            <p class="text-xl font-bold text-[var(--color-expense)]">{{ fmtAmt(monthlyData.totals.expense) }}</p>
            <p class="text-[10px] text-[var(--color-surface-500)]">{{ monthlyData.totals.expense_count }} transactions</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Balance</p>
            <p class="text-xl font-bold" :class="monthlyData.totals.balance >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
              {{ fmtAmt(monthlyData.totals.balance) }}
            </p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Savings Rate</p>
            <p class="text-xl font-bold text-[var(--color-surface-900)]">{{ (monthlyData.totals.savings_rate || 0).toFixed(1) }}%</p>
          </div>
        </div>

        <!-- Charts row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
          <!-- Daily spending bar -->
          <div class="glass-card p-5">
            <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Daily Spending</h3>
            <v-chart :option="monthlyDailyChart" autoresize style="height: 240px" />
          </div>
          <!-- Expense categories pie -->
          <div class="glass-card p-5">
            <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Expense Breakdown</h3>
            <v-chart :option="monthlyExpensePie" autoresize style="height: 240px" />
          </div>
        </div>

        <!-- Category tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <CategoryTable title="Expense Categories" :categories="monthlyData.expense_categories" />
          <CategoryTable title="Income Categories" :categories="monthlyData.income_categories" />
        </div>
      </template>
    </template>

    <!-- Yearly Report -->
    <template v-else-if="activeTab === 'yearly'">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-chevron-left" severity="secondary" text @click="yearlyYear--; fetchYearly()" />
          <span class="text-lg font-semibold text-[var(--color-surface-300)] min-w-[80px] text-center">{{ yearlyYear }}</span>
          <Button icon="pi pi-chevron-right" severity="secondary" text @click="yearlyYear++; fetchYearly()" />
        </div>
        <Button v-if="yearlyData" icon="pi pi-file-pdf" label="Export PDF" severity="secondary" text size="small"
          @click="exportYearlyPdf(yearlyData, yearlyYear)" />
      </div>

      <template v-if="yearlyData">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Income</p>
            <p class="text-xl font-bold text-[var(--color-income)]">{{ fmtAmt(yearlyData.totals.income) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Expenses</p>
            <p class="text-xl font-bold text-[var(--color-expense)]">{{ fmtAmt(yearlyData.totals.expense) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Net Balance</p>
            <p class="text-xl font-bold" :class="yearlyData.totals.balance >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
              {{ fmtAmt(yearlyData.totals.balance) }}
            </p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Savings Rate</p>
            <p class="text-xl font-bold text-[var(--color-surface-900)]">{{ (yearlyData.totals.savings_rate || 0).toFixed(1) }}%</p>
          </div>
        </div>

        <div class="glass-card p-5 mb-4">
          <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Monthly Trend</h3>
          <v-chart :option="yearlyTrendChart" autoresize style="height: 300px" />
        </div>

        <CategoryTable title="Expense Categories" :categories="yearlyData.expense_categories" />
      </template>
    </template>

    <!-- Income vs Expense Comparison -->
    <template v-else-if="activeTab === 'comparison'">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-chevron-left" severity="secondary" text @click="compYear--; fetchComparison()" />
          <span class="text-lg font-semibold text-[var(--color-surface-300)] min-w-[80px] text-center">{{ compYear }}</span>
          <Button icon="pi pi-chevron-right" severity="secondary" text @click="compYear++; fetchComparison()" />
        </div>
        <Button v-if="compData" icon="pi pi-file-pdf" label="Export PDF" severity="secondary" text size="small"
          @click="exportComparisonPdf(compData, compYear)" />
      </div>

      <template v-if="compData">
        <!-- Summary row -->
        <div class="grid grid-cols-3 gap-4 mb-4">
          <div class="glass-card p-4 text-center">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Income</p>
            <p class="text-xl font-bold text-[var(--color-income)]">{{ fmtAmt(compTotals.income) }}</p>
          </div>
          <div class="glass-card p-4 text-center">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Expenses</p>
            <p class="text-xl font-bold text-[var(--color-expense)]">{{ fmtAmt(compTotals.expense) }}</p>
          </div>
          <div class="glass-card p-4 text-center">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Net Savings</p>
            <p class="text-xl font-bold" :class="compTotals.balance >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
              {{ fmtAmt(compTotals.balance) }}
            </p>
          </div>
        </div>

        <div class="glass-card p-5 mb-4">
          <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Income vs Expense by Month</h3>
          <v-chart :option="compChart" autoresize style="height: 320px" />
        </div>

        <!-- Monthly table -->
        <div class="glass-card overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-[var(--color-surface-700)]">
                <th class="text-left py-3 px-4 text-[var(--color-surface-500)] font-medium">Month</th>
                <th class="text-right py-3 px-4 text-[var(--color-surface-500)] font-medium">Income</th>
                <th class="text-right py-3 px-4 text-[var(--color-surface-500)] font-medium">Expenses</th>
                <th class="text-right py-3 px-4 text-[var(--color-surface-500)] font-medium">Balance</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in compData"
                :key="row.month"
                class="border-b border-[var(--color-surface-800)]"
              >
                <td class="py-2.5 px-4 text-[var(--color-surface-300)]">{{ monthNames[row.month - 1] }}</td>
                <td class="py-2.5 px-4 text-right text-[var(--color-income)]">{{ fmtAmt(row.income) }}</td>
                <td class="py-2.5 px-4 text-right text-[var(--color-expense)]">{{ fmtAmt(row.expense) }}</td>
                <td class="py-2.5 px-4 text-right font-medium" :class="row.balance >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
                  {{ fmtAmt(row.balance) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </template>

    <!-- Budget Performance -->
    <template v-else-if="activeTab === 'budget'">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <Button icon="pi pi-chevron-left" severity="secondary" text @click="budgetYear--; fetchBudgetPerf()" />
          <span class="text-lg font-semibold text-[var(--color-surface-300)] min-w-[80px] text-center">{{ budgetYear }}</span>
          <Button icon="pi pi-chevron-right" severity="secondary" text @click="budgetYear++; fetchBudgetPerf()" />
        </div>
        <Button v-if="budgetPerfData" icon="pi pi-file-pdf" label="Export PDF" severity="secondary" text size="small"
          @click="exportBudgetPerfPdf(budgetPerfData)" />
      </div>

      <template v-if="budgetPerfData">
        <!-- Summary cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Budgeted</p>
            <p class="text-xl font-bold text-[var(--color-surface-900)]">{{ fmtAmt(budgetPerfData.summary.total_budgeted) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Spent</p>
            <p class="text-xl font-bold text-[var(--color-expense)]">{{ fmtAmt(budgetPerfData.summary.total_spent) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Overall Usage</p>
            <p class="text-xl font-bold" :class="budgetPerfData.summary.overall_percentage > 100 ? 'text-[var(--color-expense)]' : 'text-[var(--color-income)]'">
              {{ budgetPerfData.summary.overall_percentage }}%
            </p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Over Budget</p>
            <p class="text-xl font-bold" :class="budgetPerfData.summary.over_budget_count > 0 ? 'text-[var(--color-expense)]' : 'text-[var(--color-income)]'">
              {{ budgetPerfData.summary.over_budget_count }} / {{ budgetPerfData.summary.total_budgets }}
            </p>
          </div>
        </div>

        <!-- Monthly breakdown -->
        <div v-for="month in budgetPerfData.months" :key="month.month" class="glass-card p-5 mb-4">
          <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-3">{{ monthNames[month.month - 1] }}</h3>
          <div class="space-y-3">
            <div v-for="b in month.budgets" :key="b.id" class="flex items-center gap-3">
              <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: (b.category_color || '#6366f1') + '20', color: b.category_color || '#6366f1' }">
                <i :class="b.category_icon || 'pi pi-wallet'" class="text-xs" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-medium text-[var(--color-surface-300)] truncate">{{ b.category_name || 'General' }}</span>
                  <span class="text-xs text-[var(--color-surface-500)]">{{ fmtAmt(b.spent) }} / {{ fmtAmt(b.amount) }}</span>
                </div>
                <div class="w-full h-2 rounded-full bg-[var(--color-surface-700)] overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-500"
                    :style="{ width: Math.min(b.percentage || 0, 100) + '%', backgroundColor: b.percentage >= 90 ? '#f43f5e' : b.percentage >= 70 ? '#f59e0b' : '#6366f1' }" />
                </div>
              </div>
              <span class="text-xs font-medium w-12 text-right" :class="b.percentage > 100 ? 'text-[var(--color-expense)]' : 'text-[var(--color-surface-500)]'">
                {{ (b.percentage || 0).toFixed(0) }}%
              </span>
            </div>
          </div>
        </div>

        <div v-if="!budgetPerfData.months.length" class="glass-card p-8 text-center">
          <i class="pi pi-chart-bar text-4xl text-[var(--color-surface-400)] mb-3" />
          <p class="text-[var(--color-surface-500)]">No budgets set for {{ budgetYear }}</p>
        </div>
      </template>
    </template>

    <!-- Category Detail -->
    <template v-else-if="activeTab === 'category'">
      <div class="flex items-center gap-3 mb-4 flex-wrap">
        <Select
          v-model="catDetailId"
          :options="allCategories"
          optionLabel="name"
          optionValue="id"
          placeholder="Select a category"
          class="w-64"
          @change="fetchCategoryDetail"
        >
          <template #option="{ option }">
            <div class="flex items-center gap-2">
              <i :class="option.icon" :style="{ color: option.color }" />
              <span>{{ option.name }}</span>
              <span class="text-xs text-[var(--color-surface-500)] ml-auto capitalize">{{ option.type }}</span>
            </div>
          </template>
        </Select>
        <DatePicker
          v-model="catDateRange"
          selectionMode="range"
          placeholder="Date range"
          dateFormat="dd/mm/yy"
          showIcon
          class="w-64"
          @date-select="fetchCategoryDetail"
        />
      </div>

      <template v-if="catDetail">
        <!-- Summary -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Total</p>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">{{ fmtAmt(catDetail.summary.total) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Average</p>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">{{ fmtAmt(catDetail.summary.average) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Count</p>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">{{ catDetail.summary.count }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Highest</p>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">{{ fmtAmt(catDetail.summary.highest) }}</p>
          </div>
          <div class="glass-card p-4">
            <p class="text-xs text-[var(--color-surface-500)] mb-1">Lowest</p>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">{{ fmtAmt(catDetail.summary.lowest) }}</p>
          </div>
        </div>

        <!-- Transactions list -->
        <div class="glass-card overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-[var(--color-surface-700)]">
                <th class="text-left py-3 px-4 text-[var(--color-surface-500)] font-medium">Date</th>
                <th class="text-left py-3 px-4 text-[var(--color-surface-500)] font-medium">Description</th>
                <th class="text-right py-3 px-4 text-[var(--color-surface-500)] font-medium">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="txn in catDetail.transactions"
                :key="txn.id"
                class="border-b border-[var(--color-surface-800)]"
              >
                <td class="py-2.5 px-4 text-[var(--color-surface-500)]">{{ formatDate(txn.transaction_date) }}</td>
                <td class="py-2.5 px-4 text-[var(--color-surface-300)]">{{ txn.description || '—' }}</td>
                <td class="py-2.5 px-4 text-right font-medium" :class="txn.type === 'income' ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
                  {{ fmtAmt(txn.amount) }}
                </td>
              </tr>
              <tr v-if="!catDetail.transactions.length">
                <td colspan="3" class="py-8 text-center text-[var(--color-surface-500)]">No transactions for this category</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <div v-else-if="!catDetailId" class="glass-card p-8 text-center">
        <i class="pi pi-chart-pie text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">Select a category to view details</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import reportService from '@/services/reportService'
import { useCategoryStore } from '@/stores/categories'
import { useSettingsStore } from '@/stores/settings'
import { useCurrency } from '@/composables/useCurrency'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { BarChart, PieChart, LineChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import Button from 'primevue/button'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import ProgressSpinner from 'primevue/progressspinner'
import CategoryTable from '@/components/reports/CategoryTable.vue'
import { exportMonthlyPdf, exportYearlyPdf, exportComparisonPdf, exportBudgetPerfPdf } from '@/utils/pdfExport'

use([BarChart, PieChart, LineChart, GridComponent, TooltipComponent, LegendComponent, CanvasRenderer])

const categoryStore = useCategoryStore()
const settingsStore = useSettingsStore()
const { formatAmount } = useCurrency()

const activeTab = ref('monthly')
const loading = ref(false)
const now = new Date()
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

function fmtAmt(val) {
  return formatAmount(val || 0, settingsStore.currency)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function toISODate(date) {
  if (!date) return null
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const palette = [
  '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
  '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4',
  '#3b82f6', '#d946ef', '#14b8a6', '#0ea5e9', '#ef4444',
]

const tooltipStyle = {
  backgroundColor: 'rgba(15, 15, 25, 0.9)',
  borderColor: 'rgba(99, 102, 241, 0.3)',
  textStyle: { color: '#e2e8f0', fontSize: 12 },
}

// ──────── Monthly ────────
const monthlyMonth = ref(now.getMonth() + 1)
const monthlyYear = ref(now.getFullYear())
const monthlyData = ref(null)

function monthlyPrev() {
  if (monthlyMonth.value === 1) { monthlyMonth.value = 12; monthlyYear.value-- }
  else monthlyMonth.value--
  fetchMonthly()
}
function monthlyNext() {
  if (monthlyMonth.value === 12) { monthlyMonth.value = 1; monthlyYear.value++ }
  else monthlyMonth.value++
  fetchMonthly()
}

async function fetchMonthly() {
  loading.value = true
  try {
    const { data } = await reportService.monthly({ year: monthlyYear.value, month: monthlyMonth.value })
    if (data.status === 'success') monthlyData.value = data.data
  } finally { loading.value = false }
}

const monthlyDailyChart = computed(() => {
  const days = monthlyData.value?.daily_spending || []
  return {
    tooltip: { trigger: 'axis', ...tooltipStyle },
    grid: { top: 10, right: 10, bottom: 24, left: 40 },
    xAxis: { type: 'category', data: days.map(d => d.transaction_date.slice(8)), axisLabel: { color: '#94a3b8', fontSize: 10 }, axisLine: { lineStyle: { color: '#334155' } } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: '#1e293b' } }, axisLabel: { color: '#94a3b8', fontSize: 10 } },
    series: [{ type: 'bar', data: days.map(d => parseFloat(d.total)), itemStyle: { color: '#6366f1', borderRadius: [3, 3, 0, 0] } }],
  }
})

const monthlyExpensePie = computed(() => {
  const cats = monthlyData.value?.expense_categories || []
  return {
    tooltip: { trigger: 'item', ...tooltipStyle },
    series: [{
      type: 'pie', radius: ['45%', '72%'], center: ['50%', '50%'],
      label: { show: false },
      emphasis: { label: { show: true, color: '#e2e8f0', fontSize: 12, fontWeight: 'bold' } },
      data: cats.map((c, i) => ({ name: c.category_name, value: parseFloat(c.total), itemStyle: { color: c.category_color || palette[i % palette.length] } })),
    }],
  }
})

// ──────── Yearly ────────
const yearlyYear = ref(now.getFullYear())
const yearlyData = ref(null)

async function fetchYearly() {
  loading.value = true
  try {
    const { data } = await reportService.yearly({ year: yearlyYear.value })
    if (data.status === 'success') yearlyData.value = data.data
  } finally { loading.value = false }
}

const yearlyTrendChart = computed(() => {
  const trend = yearlyData.value?.monthly_trend || []
  const incomeByMonth = {}
  const expenseByMonth = {}
  trend.forEach(t => {
    if (t.type === 'income') incomeByMonth[t.month] = parseFloat(t.total)
    else expenseByMonth[t.month] = parseFloat(t.total)
  })
  return {
    tooltip: { trigger: 'axis', ...tooltipStyle },
    legend: { data: ['Income', 'Expenses'], textStyle: { color: '#94a3b8' }, bottom: 0 },
    grid: { top: 10, right: 16, bottom: 36, left: 50 },
    xAxis: { type: 'category', data: shortMonths, axisLine: { lineStyle: { color: '#334155' } }, axisLabel: { color: '#94a3b8' } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: '#1e293b' } }, axisLabel: { color: '#94a3b8' } },
    series: [
      { name: 'Income', type: 'bar', data: shortMonths.map((_, i) => incomeByMonth[i + 1] || 0), itemStyle: { color: '#10b981', borderRadius: [3, 3, 0, 0] }, barWidth: '35%' },
      { name: 'Expenses', type: 'bar', data: shortMonths.map((_, i) => expenseByMonth[i + 1] || 0), itemStyle: { color: '#f43f5e', borderRadius: [3, 3, 0, 0] }, barWidth: '35%' },
    ],
  }
})

// ──────── Income vs Expense ────────
const compYear = ref(now.getFullYear())
const compData = ref(null)

const compTotals = computed(() => {
  if (!compData.value) return { income: 0, expense: 0, balance: 0 }
  return compData.value.reduce((acc, m) => ({
    income: acc.income + parseFloat(m.income),
    expense: acc.expense + parseFloat(m.expense),
    balance: acc.balance + parseFloat(m.balance),
  }), { income: 0, expense: 0, balance: 0 })
})

async function fetchComparison() {
  loading.value = true
  try {
    const { data } = await reportService.incomeVsExpense({ year: compYear.value })
    if (data.status === 'success') compData.value = data.data
  } finally { loading.value = false }
}

const compChart = computed(() => {
  const rows = compData.value || []
  return {
    tooltip: { trigger: 'axis', ...tooltipStyle },
    legend: { data: ['Income', 'Expenses', 'Balance'], textStyle: { color: '#94a3b8' }, bottom: 0 },
    grid: { top: 10, right: 16, bottom: 36, left: 50 },
    xAxis: { type: 'category', data: shortMonths, axisLine: { lineStyle: { color: '#334155' } }, axisLabel: { color: '#94a3b8' } },
    yAxis: { type: 'value', splitLine: { lineStyle: { color: '#1e293b' } }, axisLabel: { color: '#94a3b8' } },
    series: [
      { name: 'Income', type: 'bar', data: rows.map(r => parseFloat(r.income)), itemStyle: { color: '#10b981', borderRadius: [3, 3, 0, 0] }, barWidth: '25%' },
      { name: 'Expenses', type: 'bar', data: rows.map(r => parseFloat(r.expense)), itemStyle: { color: '#f43f5e', borderRadius: [3, 3, 0, 0] }, barWidth: '25%' },
      { name: 'Balance', type: 'line', data: rows.map(r => parseFloat(r.balance)), smooth: true, lineStyle: { color: '#6366f1', width: 2 }, itemStyle: { color: '#6366f1' }, symbol: 'circle', symbolSize: 6 },
    ],
  }
})

// ──────── Budget Performance ────────
const budgetYear = ref(now.getFullYear())
const budgetPerfData = ref(null)

async function fetchBudgetPerf() {
  loading.value = true
  try {
    const { data } = await reportService.budgetPerformance({ year: budgetYear.value })
    if (data.status === 'success') budgetPerfData.value = data.data
  } finally { loading.value = false }
}

// ──────── Category Detail ────────
const catDetailId = ref(null)
const catDateRange = ref(null)
const catDetail = ref(null)

const allCategories = computed(() =>
  categoryStore.categories.filter(c => Number(c.is_archived) === 0)
)

async function fetchCategoryDetail() {
  if (!catDetailId.value) return
  loading.value = true
  try {
    const params = {}
    if (catDateRange.value?.[0]) params.date_from = toISODate(catDateRange.value[0])
    if (catDateRange.value?.[1]) params.date_to = toISODate(catDateRange.value[1])
    const { data } = await reportService.categoryDetail(catDetailId.value, params)
    if (data.status === 'success') catDetail.value = data.data
  } finally { loading.value = false }
}

// Tab change handler
watch(activeTab, (tab) => {
  if (tab === 'monthly' && !monthlyData.value) fetchMonthly()
  if (tab === 'yearly' && !yearlyData.value) fetchYearly()
  if (tab === 'comparison' && !compData.value) fetchComparison()
  if (tab === 'budget' && !budgetPerfData.value) fetchBudgetPerf()
})

onMounted(() => {
  fetchMonthly()
  categoryStore.fetchCategories()
})
</script>
