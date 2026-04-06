<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Net Worth</h2>
      <Button label="Add Entry" icon="pi pi-plus" @click="openDialog()" severity="primary" />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <template v-else>
      <!-- Net worth summary -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="glass-card p-5">
          <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Assets</p>
          <p class="text-2xl font-bold text-[var(--color-income)]">{{ fmtAmt(summary.total_assets) }}</p>
        </div>
        <div class="glass-card p-5">
          <p class="text-xs text-[var(--color-surface-500)] mb-1">Total Liabilities</p>
          <p class="text-2xl font-bold text-[var(--color-expense)]">{{ fmtAmt(summary.total_liabilities) }}</p>
        </div>
        <div class="glass-card p-5">
          <p class="text-xs text-[var(--color-surface-500)] mb-1">Net Worth</p>
          <p class="text-2xl font-bold" :class="summary.net_worth >= 0 ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'">
            {{ fmtAmt(summary.net_worth) }}
          </p>
        </div>
      </div>

      <!-- Donut chart -->
      <div v-if="entries.length" class="glass-card p-5 mb-6">
        <h3 class="text-sm font-semibold text-[var(--color-surface-700)] mb-4">Breakdown</h3>
        <v-chart :option="chartOption" autoresize style="height: 280px" />
      </div>

      <!-- Assets section -->
      <div class="mb-6">
        <h3 class="text-sm font-semibold text-[var(--color-surface-500)] uppercase tracking-wider mb-3">
          <i class="pi pi-arrow-up-right mr-1 text-[var(--color-income)]" />Assets
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="a in assets"
            :key="a.id"
            class="glass-card p-4 group"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ backgroundColor: (a.color || '#10b981') + '20', color: a.color || '#10b981' }">
                  <i :class="a.icon || 'pi pi-wallet'" class="text-sm" />
                </div>
                <p class="font-medium text-[var(--color-surface-900)] text-sm">{{ a.name }}</p>
              </div>
              <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openDialog(a)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]">
                  <i class="pi pi-pencil text-xs" />
                </button>
                <button @click="handleDelete(a)" class="p-1.5 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]">
                  <i class="pi pi-trash text-xs" />
                </button>
              </div>
            </div>
            <p class="text-lg font-bold text-[var(--color-income)]">{{ fmtAmt(a.amount) }}</p>
            <p v-if="a.notes" class="text-[10px] text-[var(--color-surface-500)] mt-1">{{ a.notes }}</p>
          </div>
          <div v-if="!assets.length" class="col-span-full text-center py-6 text-[var(--color-surface-500)] text-sm">
            No assets added yet
          </div>
        </div>
      </div>

      <!-- Liabilities section -->
      <div>
        <h3 class="text-sm font-semibold text-[var(--color-surface-500)] uppercase tracking-wider mb-3">
          <i class="pi pi-arrow-down-right mr-1 text-[var(--color-expense)]" />Liabilities
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="l in liabilities"
            :key="l.id"
            class="glass-card p-4 group"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ backgroundColor: (l.color || '#f43f5e') + '20', color: l.color || '#f43f5e' }">
                  <i :class="l.icon || 'pi pi-credit-card'" class="text-sm" />
                </div>
                <p class="font-medium text-[var(--color-surface-900)] text-sm">{{ l.name }}</p>
              </div>
              <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openDialog(l)" class="p-1.5 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]">
                  <i class="pi pi-pencil text-xs" />
                </button>
                <button @click="handleDelete(l)" class="p-1.5 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]">
                  <i class="pi pi-trash text-xs" />
                </button>
              </div>
            </div>
            <p class="text-lg font-bold text-[var(--color-expense)]">{{ fmtAmt(l.amount) }}</p>
            <p v-if="l.notes" class="text-[10px] text-[var(--color-surface-500)] mt-1">{{ l.notes }}</p>
          </div>
          <div v-if="!liabilities.length" class="col-span-full text-center py-6 text-[var(--color-surface-500)] text-sm">
            No liabilities added yet
          </div>
        </div>
      </div>
    </template>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Edit Entry' : 'New Entry'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Type</label>
            <div class="flex gap-2">
              <button type="button" @click="form.type = 'asset'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all"
                :class="form.type === 'asset' ? 'bg-[var(--color-income)] text-white' : 'bg-[var(--color-surface-100)] text-[var(--color-surface-500)]'"
              >Asset</button>
              <button type="button" @click="form.type = 'liability'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all"
                :class="form.type === 'liability' ? 'bg-[var(--color-expense)] text-white' : 'bg-[var(--color-surface-100)] text-[var(--color-surface-500)]'"
              >Liability</button>
            </div>
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="form.name" class="w-full" placeholder="e.g. Savings Account, Car Loan" />
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Amount</label>
            <InputNumber v-model="form.amount" :minFractionDigits="2" mode="decimal" class="w-full" placeholder="0.00" />
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Date</label>
            <DatePicker v-model="form.entry_date" dateFormat="yy-mm-dd" showIcon class="w-full" />
          </div>

          <!-- Icon -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Icon</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="ic in iconOptions"
                :key="ic"
                type="button"
                @click="form.icon = ic"
                class="w-9 h-9 rounded-lg flex items-center justify-center transition-all"
                :class="form.icon === ic ? 'bg-primary-500/20 ring-2 ring-primary-500' : 'bg-[var(--color-surface-100)] hover:bg-[var(--color-surface-200)]'"
              >
                <i :class="ic" class="text-sm" />
              </button>
            </div>
          </div>

          <!-- Color -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Color</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="c in colorOptions"
                :key="c"
                type="button"
                @click="form.color = c"
                class="w-7 h-7 rounded-full transition-all"
                :class="form.color === c ? 'ring-2 ring-offset-2 ring-offset-[var(--color-surface-50)]' : ''"
                :style="{ backgroundColor: c, ringColor: c }"
              />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Notes</label>
            <InputText v-model="form.notes" class="w-full" placeholder="Optional notes" />
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
import { useSettingsStore } from '@/stores/settings'
import { useCurrency } from '@/composables/useCurrency'
import netWorthService from '@/services/netWorthService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import VChart from 'vue-echarts'
import { use } from 'echarts/core'
import { PieChart } from 'echarts/charts'
import { TooltipComponent, LegendComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import ProgressSpinner from 'primevue/progressspinner'

use([PieChart, TooltipComponent, LegendComponent, CanvasRenderer])

const settingsStore = useSettingsStore()
const { formatAmount } = useCurrency()
const toast = useToast()
const confirm = useConfirm()

const loading = ref(false)
const entries = ref([])
const summary = ref({ total_assets: 0, total_liabilities: 0, net_worth: 0 })
const dialogVisible = ref(false)
const editing = ref(null)
const saving = ref(false)

const form = reactive({
  name: '',
  type: 'asset',
  amount: null,
  entry_date: null,
  icon: 'pi pi-wallet',
  color: '#10b981',
  notes: '',
})

const iconOptions = [
  'pi pi-wallet', 'pi pi-home', 'pi pi-car', 'pi pi-building', 'pi pi-credit-card',
  'pi pi-briefcase', 'pi pi-chart-line', 'pi pi-dollar', 'pi pi-gift', 'pi pi-globe',
  'pi pi-box', 'pi pi-shield', 'pi pi-heart', 'pi pi-bolt', 'pi pi-star',
]

const colorOptions = [
  '#10b981', '#6366f1', '#3b82f6', '#06b6d4', '#8b5cf6',
  '#f43f5e', '#f97316', '#f59e0b', '#84cc16', '#ec4899',
  '#d946ef', '#14b8a6', '#0ea5e9', '#ef4444', '#a855f7',
]

const assets = computed(() => entries.value.filter(e => e.type === 'asset'))
const liabilities = computed(() => entries.value.filter(e => e.type === 'liability'))

function fmtAmt(val) {
  return formatAmount(val || 0, settingsStore.currency)
}

function toISODate(date) {
  if (!date) return null
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const chartOption = computed(() => {
  const data = entries.value.map(e => ({
    name: e.name,
    value: parseFloat(e.amount),
    itemStyle: { color: e.color || (e.type === 'asset' ? '#10b981' : '#f43f5e') },
  }))
  return {
    tooltip: {
      trigger: 'item',
      backgroundColor: 'rgba(15, 15, 25, 0.9)',
      borderColor: 'rgba(99, 102, 241, 0.3)',
      textStyle: { color: '#e2e8f0', fontSize: 12 },
    },
    series: [{
      type: 'pie',
      radius: ['40%', '70%'],
      label: { show: false },
      emphasis: { label: { show: true, color: '#e2e8f0', fontSize: 12, fontWeight: 'bold' } },
      data,
    }],
  }
})

async function fetchData() {
  loading.value = true
  try {
    const { data } = await netWorthService.getAll()
    if (data.status === 'success') {
      entries.value = data.data.entries || []
      summary.value = {
        total_assets: data.data.total_assets || 0,
        total_liabilities: data.data.total_liabilities || 0,
        net_worth: data.data.net_worth || 0,
      }
    }
  } finally {
    loading.value = false
  }
}

function openDialog(entry = null) {
  editing.value = entry
  if (entry) {
    form.name = entry.name
    form.type = entry.type
    form.amount = parseFloat(entry.amount)
    form.entry_date = entry.entry_date ? new Date(entry.entry_date + 'T00:00:00') : new Date()
    form.icon = entry.icon || 'pi pi-wallet'
    form.color = entry.color || '#10b981'
    form.notes = entry.notes || ''
  } else {
    form.name = ''
    form.type = 'asset'
    form.amount = null
    form.entry_date = new Date()
    form.icon = 'pi pi-wallet'
    form.color = '#10b981'
    form.notes = ''
  }
  dialogVisible.value = true
}

async function handleSave() {
  if (!form.name) {
    toast.add({ severity: 'warn', summary: 'Name is required', life: 3000 })
    return
  }
  if (!form.amount && form.amount !== 0) {
    toast.add({ severity: 'warn', summary: 'Amount is required', life: 3000 })
    return
  }

  saving.value = true
  try {
    const payload = {
      name: form.name,
      type: form.type,
      amount: form.amount,
      entry_date: toISODate(form.entry_date),
      icon: form.icon,
      color: form.color,
      notes: form.notes,
    }

    if (editing.value) {
      await netWorthService.update(editing.value.id, payload)
      toast.add({ severity: 'success', summary: 'Entry updated', life: 3000 })
    } else {
      await netWorthService.create(payload)
      toast.add({ severity: 'success', summary: 'Entry created', life: 3000 })
    }
    dialogVisible.value = false
    await fetchData()
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

function handleDelete(entry) {
  confirm.require({
    message: `Delete ${entry.name}?`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await netWorthService.delete(entry.id)
        toast.add({ severity: 'success', summary: 'Entry deleted', life: 3000 })
        await fetchData()
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(fetchData)
</script>
