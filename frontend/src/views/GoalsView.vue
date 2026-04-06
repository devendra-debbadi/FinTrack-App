<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Savings Goals</h2>
      <div class="flex gap-2">
        <Button
          :label="showCompleted ? 'Hide Completed' : 'Show Completed'"
          :icon="showCompleted ? 'pi pi-eye-slash' : 'pi pi-eye'"
          severity="secondary"
          text
          @click="toggleCompleted"
        />
        <Button label="New Goal" icon="pi pi-plus" @click="openGoalDialog()" severity="primary" />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <!-- Goal cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="goal in goals"
        :key="goal.id"
        class="glass-card p-5 group"
        :class="{ 'opacity-60': goal.is_completed }"
      >
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <div
              class="w-10 h-10 rounded-xl flex items-center justify-center"
              :style="{ backgroundColor: (goal.color || '#10b981') + '20', color: goal.color || '#10b981' }"
            >
              <i :class="'pi ' + (goal.icon || 'pi-star')" class="text-lg" />
            </div>
            <div>
              <p class="font-semibold text-[var(--color-surface-900)]">{{ goal.name }}</p>
              <p v-if="goal.target_date" class="text-[10px] text-[var(--color-surface-500)]">
                Target: {{ formatDate(goal.target_date) }}
              </p>
            </div>
          </div>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="openGoalDialog(goal)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]" title="Edit">
              <i class="pi pi-pencil text-xs" />
            </button>
            <button @click="handleDelete(goal)" class="p-1.5 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]" title="Delete">
              <i class="pi pi-trash text-xs" />
            </button>
          </div>
        </div>

        <!-- Progress circle + amounts -->
        <div class="flex items-center gap-4 mb-3">
          <div class="relative w-16 h-16 flex-shrink-0">
            <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
              <path
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="var(--color-surface-700)"
                stroke-width="3"
              />
              <path
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                :stroke="goal.is_completed ? '#10b981' : (goal.color || '#6366f1')"
                stroke-width="3"
                stroke-linecap="round"
                :stroke-dasharray="`${Math.min(goal.percentage || 0, 100)}, 100`"
              />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-[var(--color-surface-300)]">
              {{ (goal.percentage || 0).toFixed(0) }}%
            </span>
          </div>
          <div>
            <p class="text-lg font-bold text-[var(--color-surface-900)]">
              {{ formatAmount(goal.current_amount, goal.currency) }}
            </p>
            <p class="text-xs text-[var(--color-surface-500)]">
              of {{ formatAmount(goal.target_amount, goal.currency) }}
            </p>
            <p v-if="goal.remaining > 0" class="text-[10px] text-[var(--color-surface-500)] mt-0.5">
              {{ formatAmount(goal.remaining, goal.currency) }} to go
            </p>
            <p v-else class="text-[10px] text-[var(--color-income)] font-medium mt-0.5">
              Goal reached!
            </p>
          </div>
        </div>

        <!-- Deposit button -->
        <Button
          v-if="!goal.is_completed"
          label="Add Deposit"
          icon="pi pi-plus"
          size="small"
          severity="secondary"
          outlined
          class="w-full"
          @click="openDepositDialog(goal)"
        />
      </div>

      <!-- Empty state -->
      <div v-if="goals.length === 0" class="col-span-full text-center py-12">
        <i class="pi pi-flag text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">No savings goals yet</p>
        <p class="text-xs text-[var(--color-surface-500)] mt-1">Create one to start saving towards your targets</p>
      </div>
    </div>

    <!-- Create/Edit Goal Dialog -->
    <Dialog
      v-model:visible="goalDialogVisible"
      :header="editingGoal ? 'Edit Goal' : 'New Savings Goal'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSaveGoal">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="goalForm.name" class="w-full" placeholder="e.g. Vacation, Emergency Fund" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Target Amount</label>
              <InputNumber v-model="goalForm.target_amount" :minFractionDigits="2" mode="decimal" class="w-full" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Target Date</label>
              <DatePicker v-model="goalForm.target_date" dateFormat="dd/mm/yy" showIcon class="w-full" />
            </div>
          </div>

          <!-- Icon -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Icon</label>
            <Select v-model="goalForm.icon" :options="iconOptions" optionLabel="label" optionValue="value" class="w-full">
              <template #value="{ value }">
                <div class="flex items-center gap-2">
                  <i :class="'pi ' + value" />
                  <span>{{ value }}</span>
                </div>
              </template>
              <template #option="{ option }">
                <div class="flex items-center gap-2">
                  <i :class="'pi ' + option.value" />
                  <span>{{ option.label }}</span>
                </div>
              </template>
            </Select>
          </div>

          <!-- Color -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Color</label>
            <div class="flex gap-2 flex-wrap">
              <button
                v-for="c in colorOptions"
                :key="c"
                type="button"
                @click="goalForm.color = c"
                class="w-8 h-8 rounded-lg border-2 transition-all"
                :style="{ backgroundColor: c }"
                :class="goalForm.color === c ? 'border-white scale-110' : 'border-transparent'"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="goalDialogVisible = false" />
          <Button type="submit" :label="editingGoal ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>

    <!-- Deposit Dialog -->
    <Dialog
      v-model:visible="depositDialogVisible"
      header="Add Deposit"
      modal
      class="w-full max-w-sm"
    >
      <form @submit.prevent="handleDeposit">
        <div class="space-y-4">
          <p class="text-sm text-[var(--color-surface-500)]">
            Deposit into <strong class="text-[var(--color-surface-300)]">{{ depositGoal?.name }}</strong>
          </p>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Amount</label>
            <InputNumber v-model="depositForm.amount" :minFractionDigits="2" mode="decimal" class="w-full" placeholder="0.00" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Date</label>
            <DatePicker v-model="depositForm.deposit_date" dateFormat="dd/mm/yy" showIcon class="w-full" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Note (optional)</label>
            <InputText v-model="depositForm.note" class="w-full" placeholder="e.g. Monthly savings" />
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="depositDialogVisible = false" />
          <Button type="submit" label="Deposit" icon="pi pi-plus" :loading="depositing" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useCurrency } from '@/composables/useCurrency'
import { useSettingsStore } from '@/stores/settings'
import goalService from '@/services/goalService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import ProgressSpinner from 'primevue/progressspinner'

const settingsStore = useSettingsStore()
const { formatAmount: fmt } = useCurrency()
const toast = useToast()
const confirm = useConfirm()

const goals = ref([])
const loading = ref(false)
const showCompleted = ref(false)
const goalDialogVisible = ref(false)
const depositDialogVisible = ref(false)
const editingGoal = ref(null)
const depositGoal = ref(null)
const saving = ref(false)
const depositing = ref(false)

const goalForm = reactive({
  name: '',
  target_amount: null,
  target_date: null,
  icon: 'pi-star',
  color: '#10b981',
})

const depositForm = reactive({
  amount: null,
  deposit_date: new Date(),
  note: '',
})

const iconOptions = [
  { label: 'Star', value: 'pi-star' },
  { label: 'Flag', value: 'pi-flag' },
  { label: 'Home', value: 'pi-home' },
  { label: 'Car', value: 'pi-car' },
  { label: 'Globe', value: 'pi-globe' },
  { label: 'Heart', value: 'pi-heart' },
  { label: 'Gift', value: 'pi-gift' },
  { label: 'Wallet', value: 'pi-wallet' },
  { label: 'Briefcase', value: 'pi-briefcase' },
  { label: 'Shield', value: 'pi-shield' },
  { label: 'Graduation', value: 'pi-graduation-cap' },
  { label: 'Chart', value: 'pi-chart-line' },
]

const colorOptions = [
  '#10b981', '#22c55e', '#84cc16', '#14b8a6', '#06b6d4',
  '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7',
  '#d946ef', '#ec4899', '#f43f5e', '#f97316', '#f59e0b',
]

function formatAmount(val, currency) {
  return fmt(val || 0, currency || settingsStore.currency)
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function toISODate(date) {
  if (!date) return null
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

function toggleCompleted() {
  showCompleted.value = !showCompleted.value
  fetchGoals()
}

async function fetchGoals() {
  loading.value = true
  try {
    const params = showCompleted.value ? { include_completed: 1 } : {}
    const { data } = await goalService.getAll(params)
    if (data.status === 'success') {
      goals.value = data.data
    }
  } finally {
    loading.value = false
  }
}

function openGoalDialog(goal = null) {
  editingGoal.value = goal
  if (goal) {
    goalForm.name = goal.name
    goalForm.target_amount = parseFloat(goal.target_amount)
    goalForm.target_date = goal.target_date ? new Date(goal.target_date + 'T00:00:00') : null
    goalForm.icon = goal.icon || 'pi-star'
    goalForm.color = goal.color || '#10b981'
  } else {
    goalForm.name = ''
    goalForm.target_amount = null
    goalForm.target_date = null
    goalForm.icon = 'pi-star'
    goalForm.color = '#10b981'
  }
  goalDialogVisible.value = true
}

function openDepositDialog(goal) {
  depositGoal.value = goal
  depositForm.amount = null
  depositForm.deposit_date = new Date()
  depositForm.note = ''
  depositDialogVisible.value = true
}

async function handleSaveGoal() {
  if (!goalForm.name.trim() || goalForm.name.trim().length < 2) {
    toast.add({ severity: 'warn', summary: 'Name must be at least 2 characters', life: 3000 })
    return
  }
  if (!goalForm.target_amount || goalForm.target_amount <= 0) {
    toast.add({ severity: 'warn', summary: 'Target amount must be greater than 0', life: 3000 })
    return
  }

  saving.value = true
  try {
    const payload = {
      name: goalForm.name,
      target_amount: goalForm.target_amount,
      target_date: toISODate(goalForm.target_date),
      icon: goalForm.icon,
      color: goalForm.color,
    }

    if (editingGoal.value) {
      await goalService.update(editingGoal.value.id, payload)
      toast.add({ severity: 'success', summary: 'Goal updated', life: 3000 })
    } else {
      await goalService.create(payload)
      toast.add({ severity: 'success', summary: 'Goal created', life: 3000 })
    }
    goalDialogVisible.value = false
    await fetchGoals()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

async function handleDeposit() {
  if (!depositForm.amount || depositForm.amount <= 0) {
    toast.add({ severity: 'warn', summary: 'Amount must be greater than 0', life: 3000 })
    return
  }

  depositing.value = true
  try {
    await goalService.deposit(depositGoal.value.id, {
      amount: depositForm.amount,
      deposit_date: toISODate(depositForm.deposit_date),
      note: depositForm.note,
    })
    toast.add({ severity: 'success', summary: 'Deposit added', life: 3000 })
    depositDialogVisible.value = false
    await fetchGoals()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    depositing.value = false
  }
}

function handleDelete(goal) {
  confirm.require({
    message: `Delete "${goal.name}"? All deposits will be lost.`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await goalService.delete(goal.id)
        toast.add({ severity: 'success', summary: 'Goal deleted', life: 3000 })
        await fetchGoals()
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(() => fetchGoals())
</script>
