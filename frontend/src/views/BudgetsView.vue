<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Budgets</h2>
      <Button label="Add Budget" icon="pi pi-plus" @click="openDialog()" severity="primary" />
    </div>

    <!-- Period selector -->
    <div class="flex items-center gap-3 mb-6">
      <Button icon="pi pi-chevron-left" severity="secondary" text @click="prevMonth" />
      <span class="text-lg font-semibold text-[var(--color-surface-300)] min-w-[160px] text-center">
        {{ monthLabel }}
      </span>
      <Button icon="pi pi-chevron-right" severity="secondary" text @click="nextMonth" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <!-- Budget cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="b in budgets"
        :key="b.id"
        class="glass-card p-5 group"
      >
        <!-- Header: category + actions -->
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <div
              class="w-9 h-9 rounded-lg flex items-center justify-center"
              :style="{ backgroundColor: (b.category_color || '#6366f1') + '20', color: b.category_color || '#6366f1' }"
            >
              <i :class="b.category_icon || 'pi pi-wallet'" class="text-sm" />
            </div>
            <div>
              <p class="font-medium text-[var(--color-surface-900)] text-sm">{{ b.category_name || 'General' }}</p>
              <p class="text-[10px] text-[var(--color-surface-500)] capitalize">{{ b.period }}</p>
            </div>
          </div>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="openDialog(b)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]" title="Edit">
              <i class="pi pi-pencil text-xs" />
            </button>
            <button @click="handleDelete(b)" class="p-1.5 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]" title="Delete">
              <i class="pi pi-trash text-xs" />
            </button>
          </div>
        </div>

        <!-- Amount display -->
        <div class="flex items-baseline justify-between mb-2">
          <span class="text-xl font-bold" :class="percentClass(b)">
            {{ formatAmount(b.spent, b.currency) }}
          </span>
          <span class="text-sm text-[var(--color-surface-500)]">
            of {{ formatAmount(b.amount, b.currency) }}
          </span>
        </div>

        <!-- Progress bar -->
        <div class="w-full h-2.5 rounded-full bg-[var(--color-surface-700)] overflow-hidden mb-1.5">
          <div
            class="h-full rounded-full transition-all duration-500"
            :style="{ width: Math.min(b.percentage || 0, 100) + '%', backgroundColor: barColor(b) }"
          />
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium" :class="percentClass(b)">
            {{ (b.percentage || 0).toFixed(0) }}% used
          </span>
          <span class="text-xs text-[var(--color-surface-500)]">
            {{ formatAmount(b.remaining, b.currency) }} left
          </span>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="budgets.length === 0" class="col-span-full text-center py-12">
        <i class="pi pi-chart-bar text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">No budgets for {{ monthLabel }}</p>
        <p class="text-xs text-[var(--color-surface-500)] mt-1">Create one to start tracking spending limits</p>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editingBudget ? 'Edit Budget' : 'New Budget'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Category</label>
            <Select
              v-model="form.category_id"
              :options="categoryStore.expenseCategories"
              optionLabel="name"
              optionValue="id"
              placeholder="All expenses (general budget)"
              showClear
              class="w-full"
            >
              <template #option="{ option }">
                <div class="flex items-center gap-2">
                  <i :class="option.icon" :style="{ color: option.color }" />
                  <span>{{ option.name }}</span>
                </div>
              </template>
            </Select>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Budget Limit</label>
            <InputNumber
              v-model="form.amount"
              :minFractionDigits="2"
              mode="decimal"
              class="w-full"
              placeholder="0.00"
            />
          </div>

          <!-- Period -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Period</label>
            <Select
              v-model="form.period"
              :options="periodOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>

          <!-- Month + Year -->
          <div class="grid grid-cols-2 gap-3">
            <div v-if="form.period === 'monthly'">
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Month</label>
              <Select
                v-model="form.month"
                :options="monthOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
              />
            </div>
            <div :class="form.period === 'monthly' ? '' : 'col-span-2'">
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Year</label>
              <InputNumber v-model="form.year" :useGrouping="false" class="w-full" />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="dialogVisible = false" />
          <Button type="submit" :label="editingBudget ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/categories'
import { useCurrency } from '@/composables/useCurrency'
import { useSettingsStore } from '@/stores/settings'
import budgetService from '@/services/budgetService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import ProgressSpinner from 'primevue/progressspinner'

const categoryStore = useCategoryStore()
const settingsStore = useSettingsStore()
const { formatAmount: fmt } = useCurrency()
const toast = useToast()
const confirm = useConfirm()

const budgets = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const editingBudget = ref(null)
const saving = ref(false)

const now = new Date()
const selectedMonth = ref(now.getMonth() + 1)
const selectedYear = ref(now.getFullYear())

const form = reactive({
  category_id: null,
  amount: null,
  period: 'monthly',
  month: now.getMonth() + 1,
  year: now.getFullYear(),
})

const periodOptions = [
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' },
]

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const monthOptions = monthNames.map((m, i) => ({ label: m, value: i + 1 }))

const monthLabel = computed(() => `${monthNames[selectedMonth.value - 1]} ${selectedYear.value}`)

function prevMonth() {
  if (selectedMonth.value === 1) {
    selectedMonth.value = 12
    selectedYear.value--
  } else {
    selectedMonth.value--
  }
  fetchBudgets()
}

function nextMonth() {
  if (selectedMonth.value === 12) {
    selectedMonth.value = 1
    selectedYear.value++
  } else {
    selectedMonth.value++
  }
  fetchBudgets()
}

function formatAmount(val, currency) {
  return fmt(val || 0, currency || settingsStore.currency)
}

function barColor(b) {
  const p = b.percentage || 0
  if (p >= 90) return '#f43f5e'
  if (p >= 70) return '#f59e0b'
  return '#6366f1'
}

function percentClass(b) {
  const p = b.percentage || 0
  if (p >= 90) return 'text-[var(--color-expense)]'
  if (p >= 70) return 'text-[#f59e0b]'
  return 'text-[var(--color-surface-900)]'
}

async function fetchBudgets() {
  loading.value = true
  try {
    const { data } = await budgetService.getAll({ year: selectedYear.value, month: selectedMonth.value })
    if (data.status === 'success') {
      budgets.value = data.data
    }
  } finally {
    loading.value = false
  }
}

function openDialog(budget = null) {
  editingBudget.value = budget
  if (budget) {
    form.category_id = budget.category_id
    form.amount = parseFloat(budget.amount)
    form.period = budget.period
    form.month = budget.month || selectedMonth.value
    form.year = budget.year
  } else {
    form.category_id = null
    form.amount = null
    form.period = 'monthly'
    form.month = selectedMonth.value
    form.year = selectedYear.value
  }
  dialogVisible.value = true
}

async function handleSave() {
  if (!form.amount || form.amount <= 0) {
    toast.add({ severity: 'warn', summary: 'Budget limit must be greater than 0', life: 3000 })
    return
  }

  saving.value = true
  try {
    const payload = {
      category_id: form.category_id,
      amount: form.amount,
      period: form.period,
      year: form.year,
    }
    if (form.period === 'monthly') payload.month = form.month

    if (editingBudget.value) {
      await budgetService.update(editingBudget.value.id, payload)
      toast.add({ severity: 'success', summary: 'Budget updated', life: 3000 })
    } else {
      await budgetService.create(payload)
      toast.add({ severity: 'success', summary: 'Budget created', life: 3000 })
    }
    dialogVisible.value = false
    await fetchBudgets()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

function handleDelete(budget) {
  confirm.require({
    message: `Delete this budget for ${budget.category_name || 'General'}?`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await budgetService.delete(budget.id)
        toast.add({ severity: 'success', summary: 'Budget deleted', life: 3000 })
        await fetchBudgets()
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(() => {
  fetchBudgets()
  categoryStore.fetchCategories()
})
</script>
