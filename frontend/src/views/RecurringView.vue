<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Recurring Transactions</h2>
      <Button label="Add Recurring" icon="pi pi-plus" @click="openDialog()" severity="primary" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <!-- Cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="item in items"
        :key="item.id"
        class="glass-card p-5 group"
        :class="{ 'opacity-50': Number(item.is_active) === 0 }"
      >
        <!-- Header row -->
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <div
              class="w-9 h-9 rounded-lg flex items-center justify-center"
              :style="{ backgroundColor: (item.category_color || '#6366f1') + '20', color: item.category_color || '#6366f1' }"
            >
              <i :class="item.category_icon || 'pi pi-wallet'" class="text-sm" />
            </div>
            <div>
              <p class="font-medium text-[var(--color-surface-900)] text-sm">{{ item.description || item.category_name }}</p>
              <p class="text-[10px] text-[var(--color-surface-500)]">{{ item.category_name }}</p>
            </div>
          </div>
          <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="handleProcess(item)" class="p-1.5 rounded-lg hover:bg-primary-500/10 text-primary-400" title="Generate transaction">
              <i class="pi pi-play text-xs" />
            </button>
            <button @click="handleToggle(item)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]" :title="Number(item.is_active) === 1 ? 'Pause' : 'Activate'">
              <i :class="Number(item.is_active) === 1 ? 'pi pi-pause' : 'pi pi-play'" class="text-xs" />
            </button>
            <button @click="openDialog(item)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]" title="Edit">
              <i class="pi pi-pencil text-xs" />
            </button>
            <button @click="handleDelete(item)" class="p-1.5 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]" title="Delete">
              <i class="pi pi-trash text-xs" />
            </button>
          </div>
        </div>

        <!-- Amount -->
        <div class="flex items-baseline justify-between mb-2">
          <span class="text-xl font-bold" :class="item.type === 'income' ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
            {{ item.type === 'income' ? '+' : '-' }}{{ formatAmount(item.amount, item.currency) }}
          </span>
          <span class="text-xs px-2 py-0.5 rounded-full capitalize"
            :class="item.type === 'income' ? 'bg-[var(--color-income)]/10 text-[var(--color-income)]' : 'bg-[var(--color-expense)]/10 text-[var(--color-expense)]'"
          >
            {{ item.type }}
          </span>
        </div>

        <!-- Frequency + next due -->
        <div class="flex items-center justify-between text-xs">
          <span class="text-[var(--color-surface-500)] capitalize">
            <i class="pi pi-sync mr-1" />{{ item.frequency }}
          </span>
          <span class="text-[var(--color-surface-500)]">
            Next: {{ formatDate(item.next_due) }}
          </span>
        </div>

        <!-- Status badge -->
        <div class="mt-2">
          <span v-if="Number(item.is_active) === 0" class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400">
            Paused
          </span>
          <span v-else-if="isDue(item.next_due)" class="text-[10px] px-2 py-0.5 rounded-full bg-[var(--color-expense)]/10 text-[var(--color-expense)]">
            Due
          </span>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="items.length === 0" class="col-span-full text-center py-12">
        <i class="pi pi-sync text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">No recurring transactions</p>
        <p class="text-xs text-[var(--color-surface-500)] mt-1">Automate your regular income and expenses</p>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit Recurring Transaction' : 'New Recurring Transaction'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <!-- Type toggle -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Type</label>
            <div class="flex gap-2">
              <button
                type="button"
                @click="form.type = 'expense'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all"
                :class="form.type === 'expense'
                  ? 'bg-[var(--color-expense)] text-white'
                  : 'bg-[var(--color-surface-100)] text-[var(--color-surface-500)] hover:bg-[var(--color-surface-200)]'"
              >Expense</button>
              <button
                type="button"
                @click="form.type = 'income'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all"
                :class="form.type === 'income'
                  ? 'bg-[var(--color-income)] text-white'
                  : 'bg-[var(--color-surface-100)] text-[var(--color-surface-500)] hover:bg-[var(--color-surface-200)]'"
              >Income</button>
            </div>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Amount</label>
            <InputNumber v-model="form.amount" :minFractionDigits="2" mode="decimal" class="w-full" placeholder="0.00" />
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Category</label>
            <Select
              v-model="form.category_id"
              :options="filteredCategories"
              optionLabel="name"
              optionValue="id"
              placeholder="Select category"
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

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Description</label>
            <InputText v-model="form.description" class="w-full" placeholder="e.g. Monthly rent" />
          </div>

          <!-- Frequency -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Frequency</label>
            <Select
              v-model="form.frequency"
              :options="frequencyOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>

          <!-- Next Due Date -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Next Due Date</label>
            <DatePicker v-model="form.next_due" dateFormat="yy-mm-dd" showIcon class="w-full" />
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="dialogVisible = false" />
          <Button type="submit" :label="editing ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/categories'
import { useSettingsStore } from '@/stores/settings'
import { useCurrency } from '@/composables/useCurrency'
import recurringService from '@/services/recurringService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import ProgressSpinner from 'primevue/progressspinner'

const categoryStore = useCategoryStore()
const settingsStore = useSettingsStore()
const { formatAmount: fmt } = useCurrency()
const toast = useToast()
const confirm = useConfirm()

const items = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const editing = ref(null)
const saving = ref(false)

const form = reactive({
  type: 'expense',
  amount: null,
  category_id: null,
  description: '',
  frequency: 'monthly',
  next_due: null,
})

const frequencyOptions = [
  { label: 'Daily', value: 'daily' },
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Yearly', value: 'yearly' },
]

const filteredCategories = computed(() => {
  if (form.type === 'income') return categoryStore.incomeCategories
  return categoryStore.expenseCategories
})

function formatAmount(val, currency) {
  return fmt(val || 0, currency || settingsStore.currency)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function isDue(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr + 'T00:00:00') <= new Date()
}

function toISODate(date) {
  if (!date) return null
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

async function fetchItems() {
  loading.value = true
  try {
    const { data } = await recurringService.getAll()
    if (data.status === 'success') items.value = data.data
  } finally {
    loading.value = false
  }
}

function openDialog(item = null) {
  editing.value = item
  if (item) {
    form.type = item.type
    form.amount = parseFloat(item.amount)
    form.category_id = parseInt(item.category_id)
    form.description = item.description || ''
    form.frequency = item.frequency
    form.next_due = item.next_due ? new Date(item.next_due + 'T00:00:00') : null
  } else {
    form.type = 'expense'
    form.amount = null
    form.category_id = null
    form.description = ''
    form.frequency = 'monthly'
    form.next_due = new Date()
  }
  dialogVisible.value = true
}

async function handleSave() {
  if (!form.amount || form.amount <= 0) {
    toast.add({ severity: 'warn', summary: 'Amount must be greater than 0', life: 3000 })
    return
  }
  if (!form.category_id) {
    toast.add({ severity: 'warn', summary: 'Please select a category', life: 3000 })
    return
  }

  saving.value = true
  try {
    const payload = {
      type: form.type,
      amount: form.amount,
      category_id: form.category_id,
      description: form.description,
      frequency: form.frequency,
      next_due: toISODate(form.next_due),
    }

    if (editing.value) {
      await recurringService.update(editing.value.id, payload)
      toast.add({ severity: 'success', summary: 'Recurring transaction updated', life: 3000 })
    } else {
      await recurringService.create(payload)
      toast.add({ severity: 'success', summary: 'Recurring transaction created', life: 3000 })
    }
    dialogVisible.value = false
    await fetchItems()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

async function handleToggle(item) {
  try {
    await recurringService.toggle(item.id)
    toast.add({ severity: 'success', summary: Number(item.is_active) === 1 ? 'Paused' : 'Activated', life: 3000 })
    await fetchItems()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  }
}

async function handleProcess(item) {
  confirm.require({
    message: `Generate a transaction of ${formatAmount(item.amount, item.currency)} from this recurring entry?`,
    header: 'Generate Transaction',
    icon: 'pi pi-play',
    rejectLabel: 'Cancel',
    acceptLabel: 'Generate',
    accept: async () => {
      try {
        await recurringService.process(item.id)
        toast.add({ severity: 'success', summary: 'Transaction generated', life: 3000 })
        await fetchItems()
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

function handleDelete(item) {
  confirm.require({
    message: `Delete this recurring ${item.type} for ${item.description || item.category_name}?`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await recurringService.delete(item.id)
        toast.add({ severity: 'success', summary: 'Recurring transaction deleted', life: 3000 })
        await fetchItems()
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(() => {
  fetchItems()
  categoryStore.fetchCategories()
})
</script>
