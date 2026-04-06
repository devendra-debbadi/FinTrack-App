<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Transactions</h2>
      <div class="flex gap-2">
        <Button icon="pi pi-download" label="Export" severity="secondary" text @click="handleExport" :loading="exporting" />
        <Button icon="pi pi-upload" label="Import" severity="secondary" text @click="triggerImport" />
        <input ref="importInput" type="file" accept=".csv" class="hidden" @change="handleImport" />
        <Button label="Add Transaction" icon="pi pi-plus" @click="openDialog()" severity="primary" />
      </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4 mb-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Search -->
        <IconField>
          <InputIcon class="pi pi-search" />
          <InputText
            v-model="txnStore.filters.search"
            placeholder="Search description..."
            class="w-full"
            @keyup.enter="applyFilters"
          />
        </IconField>

        <!-- Type filter -->
        <Select
          v-model="txnStore.filters.type"
          :options="typeFilterOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="All Types"
          showClear
          class="w-full"
        />

        <!-- Category filter -->
        <Select
          v-model="txnStore.filters.category_id"
          :options="allActiveCategories"
          optionLabel="name"
          optionValue="id"
          placeholder="All Categories"
          showClear
          class="w-full"
        >
          <template #option="{ option }">
            <div class="flex items-center gap-2">
              <i :class="option.icon" :style="{ color: option.color }" />
              <span>{{ option.name }}</span>
              <span class="text-xs text-[var(--color-surface-500)] ml-auto capitalize">{{ option.type }}</span>
            </div>
          </template>
        </Select>

        <!-- Date range -->
        <DatePicker
          v-model="dateRange"
          selectionMode="range"
          placeholder="Date range"
          dateFormat="dd/mm/yy"
          showIcon
          class="w-full"
        />
      </div>

      <!-- Second row: amount range + sort + actions -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
        <InputNumber
          v-model="txnStore.filters.min_amount"
          placeholder="Min amount"
          :minFractionDigits="2"
          mode="decimal"
          class="w-full"
        />
        <InputNumber
          v-model="txnStore.filters.max_amount"
          placeholder="Max amount"
          :minFractionDigits="2"
          mode="decimal"
          class="w-full"
        />
        <Select
          v-model="sortOption"
          :options="sortOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="Sort by"
          class="w-full"
        />
        <div class="flex gap-2">
          <Button label="Apply" icon="pi pi-filter" @click="applyFilters" class="flex-1" severity="primary" />
          <Button icon="pi pi-filter-slash" @click="resetFilters" severity="secondary" text title="Reset filters" />
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="glass-card overflow-hidden">
      <div v-if="txnStore.loading" class="flex justify-center py-12">
        <ProgressSpinner strokeWidth="3" />
      </div>

      <DataTable
        v-else
        :value="txnStore.transactions"
        stripedRows
        :rowHover="true"
        class="p-datatable-sm"
      >
        <template #empty>
          <div class="text-center py-12">
            <i class="pi pi-receipt text-4xl text-[var(--color-surface-400)] mb-3" />
            <p class="text-[var(--color-surface-500)]">No transactions found</p>
          </div>
        </template>

        <!-- Date -->
        <Column header="Date" style="min-width: 110px">
          <template #body="{ data }">
            <span class="text-sm">{{ formatDate(data.transaction_date) }}</span>
          </template>
        </Column>

        <!-- Type badge -->
        <Column header="Type" style="min-width: 90px">
          <template #body="{ data }">
            <Tag
              :value="data.type"
              :severity="data.type === 'income' ? 'success' : 'danger'"
              class="capitalize text-xs"
            />
          </template>
        </Column>

        <!-- Category -->
        <Column header="Category" style="min-width: 140px">
          <template #body="{ data }">
            <div class="flex items-center gap-2">
              <i :class="data.category_icon" :style="{ color: data.category_color }" class="text-sm" />
              <span class="text-sm">{{ data.category_name || '—' }}</span>
            </div>
          </template>
        </Column>

        <!-- Description -->
        <Column header="Description" style="min-width: 200px">
          <template #body="{ data }">
            <div>
              <p class="text-sm text-[var(--color-surface-900)] truncate max-w-xs">{{ data.description || '—' }}</p>
              <div v-if="data.tags?.length" class="flex gap-1 mt-1">
                <span
                  v-for="tag in data.tags"
                  :key="tag.id"
                  class="text-[10px] px-1.5 py-0.5 rounded bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)]"
                >
                  {{ tag.name }}
                </span>
              </div>
            </div>
          </template>
        </Column>

        <!-- Amount -->
        <Column header="Amount" style="min-width: 120px">
          <template #body="{ data }">
            <span
              class="font-semibold text-sm"
              :class="data.type === 'income' ? 'text-[var(--color-income)]' : 'text-[var(--color-expense)]'"
            >
              {{ data.type === 'income' ? '+' : '-' }}{{ formatAmount(data.amount, data.currency) }}
            </span>
          </template>
        </Column>

        <!-- Actions -->
        <Column header="" style="width: 100px">
          <template #body="{ data }">
            <div class="flex gap-1 justify-end">
              <button
                @click="openDialog(data)"
                class="p-2 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]"
                title="Edit"
              >
                <i class="pi pi-pencil text-sm" />
              </button>
              <button
                @click="handleDelete(data)"
                class="p-2 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]"
                title="Delete"
              >
                <i class="pi pi-trash text-sm" />
              </button>
            </div>
          </template>
        </Column>
      </DataTable>

      <!-- Pagination -->
      <div
        v-if="txnStore.pagination.total_pages > 1"
        class="flex items-center justify-between px-4 py-3 border-t border-[var(--color-surface-200)]"
      >
        <span class="text-sm text-[var(--color-surface-500)]">
          Showing {{ rangeStart }}–{{ rangeEnd }} of {{ txnStore.pagination.total }}
        </span>
        <Paginator
          :rows="txnStore.pagination.per_page"
          :totalRecords="txnStore.pagination.total"
          :first="(txnStore.pagination.page - 1) * txnStore.pagination.per_page"
          @page="onPageChange"
          template="PrevPageLink PageLinks NextPageLink"
        />
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editingTxn ? 'Edit Transaction' : 'New Transaction'"
      modal
      class="w-full max-w-lg"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Type</label>
            <div class="flex gap-2">
              <button
                type="button"
                @click="form.type = 'expense'"
                class="flex-1 py-2 px-4 rounded-lg border-2 text-sm font-medium transition-all"
                :class="form.type === 'expense'
                  ? 'border-[var(--color-expense)] bg-[var(--color-expense)]/10 text-[var(--color-expense)]'
                  : 'border-[var(--color-surface-300)] text-[var(--color-surface-500)]'"
              >
                <i class="pi pi-arrow-up-right mr-1" /> Expense
              </button>
              <button
                type="button"
                @click="form.type = 'income'"
                class="flex-1 py-2 px-4 rounded-lg border-2 text-sm font-medium transition-all"
                :class="form.type === 'income'
                  ? 'border-[var(--color-income)] bg-[var(--color-income)]/10 text-[var(--color-income)]'
                  : 'border-[var(--color-surface-300)] text-[var(--color-surface-500)]'"
              >
                <i class="pi pi-arrow-down-left mr-1" /> Income
              </button>
            </div>
          </div>

          <!-- Amount + Currency -->
          <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Amount</label>
              <InputNumber
                v-model="form.amount"
                :minFractionDigits="2"
                mode="decimal"
                class="w-full"
                placeholder="0.00"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Currency</label>
              <Select
                v-model="form.currency"
                :options="currencyOptions"
                optionLabel="label"
                optionValue="value"
                class="w-full"
              />
            </div>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Category</label>
            <Select
              v-model="form.category_id"
              :options="formCategories"
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

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Date</label>
            <DatePicker
              v-model="form.transaction_date"
              dateFormat="dd/mm/yy"
              showIcon
              class="w-full"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Description</label>
            <InputText v-model="form.description" class="w-full" placeholder="What was this for?" />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Notes</label>
            <Textarea v-model="form.notes" class="w-full" rows="2" placeholder="Additional notes (optional)" />
          </div>

          <!-- Tags -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Tags</label>
            <div class="flex gap-2 flex-wrap mb-2">
              <span
                v-for="tag in form.tags"
                :key="tag"
                class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)]"
              >
                {{ tag }}
                <i class="pi pi-times text-[10px] cursor-pointer" @click="removeTag(tag)" />
              </span>
            </div>
            <div class="flex gap-2">
              <InputText
                v-model="newTag"
                class="flex-1"
                placeholder="Add a tag..."
                @keyup.enter.prevent="addTag"
              />
              <Button type="button" icon="pi pi-plus" @click="addTag" severity="secondary" text size="small" />
            </div>
          </div>

          <!-- Receipt (only for existing transactions) -->
          <div v-if="editingTxn">
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Receipt</label>
            <!-- Existing receipts -->
            <div v-if="receipts.length" class="space-y-2 mb-2">
              <div
                v-for="r in receipts"
                :key="r.id"
                class="flex items-center justify-between p-2 rounded-lg bg-[var(--color-surface-800)]"
              >
                <div class="flex items-center gap-2 text-sm text-[var(--color-surface-300)]">
                  <i class="pi pi-file text-xs" />
                  <span class="truncate max-w-[200px]">{{ r.original_name || 'Receipt' }}</span>
                </div>
                <button type="button" @click="deleteReceipt(r.id)" class="text-[var(--color-expense)] hover:text-[var(--color-expense)]/80">
                  <i class="pi pi-times text-xs" />
                </button>
              </div>
            </div>
            <div class="flex gap-2">
              <input ref="receiptInput" type="file" accept="image/*,.pdf" class="hidden" @change="uploadReceipt" />
              <Button
                type="button"
                label="Upload Receipt"
                icon="pi pi-camera"
                severity="secondary"
                outlined
                size="small"
                @click="$refs.receiptInput.click()"
                :loading="uploadingReceipt"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="dialogVisible = false" />
          <Button type="submit" :label="editingTxn ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useTransactionStore } from '@/stores/transactions'
import { useCategoryStore } from '@/stores/categories'
import { useSettingsStore } from '@/stores/settings'
import { useCurrency } from '@/composables/useCurrency'
import reportService from '@/services/reportService'
import receiptService from '@/services/receiptService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Textarea from 'primevue/textarea'
import Tag from 'primevue/tag'
import Paginator from 'primevue/paginator'
import ProgressSpinner from 'primevue/progressspinner'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const txnStore = useTransactionStore()
const categoryStore = useCategoryStore()
const settingsStore = useSettingsStore()
const { formatAmount } = useCurrency()
const toast = useToast()
const confirm = useConfirm()

const dialogVisible = ref(false)
const editingTxn = ref(null)
const saving = ref(false)
const newTag = ref('')
const dateRange = ref(null)
const exporting = ref(false)
const importInput = ref(null)
const receiptInput = ref(null)
const uploadingReceipt = ref(false)
const receipts = ref([])

const form = reactive({
  type: 'expense',
  amount: null,
  currency: 'EUR',
  category_id: null,
  transaction_date: new Date(),
  description: '',
  notes: '',
  tags: [],
})

// Sort mapping
const sortOption = ref('transaction_date_DESC')

const sortOptions = [
  { label: 'Date (newest)', value: 'transaction_date_DESC' },
  { label: 'Date (oldest)', value: 'transaction_date_ASC' },
  { label: 'Amount (high)', value: 'amount_DESC' },
  { label: 'Amount (low)', value: 'amount_ASC' },
  { label: 'Description A-Z', value: 'description_ASC' },
  { label: 'Description Z-A', value: 'description_DESC' },
]

const typeFilterOptions = [
  { label: 'Expense', value: 'expense' },
  { label: 'Income', value: 'income' },
]

const currencyOptions = [
  { label: '€ EUR', value: 'EUR' },
  { label: '$ USD', value: 'USD' },
  { label: '£ GBP', value: 'GBP' },
  { label: '₹ INR', value: 'INR' },
  { label: '¥ JPY', value: 'JPY' },
  { label: 'CA$ CAD', value: 'CAD' },
  { label: 'A$ AUD', value: 'AUD' },
  { label: 'CHF', value: 'CHF' },
]

// Categories filtered by selected type in form
const formCategories = computed(() => {
  if (form.type === 'income') return categoryStore.incomeCategories
  return categoryStore.expenseCategories
})

// All active categories for the filter dropdown
const allActiveCategories = computed(() =>
  categoryStore.categories.filter(c => Number(c.is_archived) === 0)
)

// Pagination display
const rangeStart = computed(() =>
  (txnStore.pagination.page - 1) * txnStore.pagination.per_page + 1
)
const rangeEnd = computed(() =>
  Math.min(
    txnStore.pagination.page * txnStore.pagination.per_page,
    txnStore.pagination.total
  )
)

// Reset category when type changes in form
watch(() => form.type, () => {
  form.category_id = null
})

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr + 'T00:00:00')
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function applyFilters() {
  // Parse sort option
  const [sort, direction] = sortOption.value.split('_')
  txnStore.filters.sort = sort
  txnStore.filters.direction = direction

  // Parse date range
  if (dateRange.value && dateRange.value[0]) {
    txnStore.filters.date_from = toISODate(dateRange.value[0])
    txnStore.filters.date_to = dateRange.value[1] ? toISODate(dateRange.value[1]) : null
  } else {
    txnStore.filters.date_from = null
    txnStore.filters.date_to = null
  }

  txnStore.fetchTransactions(1)
}

function resetFilters() {
  txnStore.resetFilters()
  dateRange.value = null
  sortOption.value = 'transaction_date_DESC'
  txnStore.fetchTransactions(1)
}

function onPageChange(event) {
  const page = Math.floor(event.first / event.rows) + 1
  txnStore.fetchTransactions(page)
}

function toISODate(date) {
  if (!date) return null
  const d = new Date(date)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

function openDialog(txn = null) {
  editingTxn.value = txn
  receipts.value = []
  if (txn) {
    form.type = txn.type
    form.amount = parseFloat(txn.amount)
    form.currency = txn.currency || settingsStore.currency
    form.category_id = txn.category_id
    form.transaction_date = new Date(txn.transaction_date + 'T00:00:00')
    form.description = txn.description || ''
    form.notes = txn.notes || ''
    form.tags = txn.tags ? txn.tags.map(t => t.name) : []
    fetchReceipts(txn.id)
  } else {
    form.type = 'expense'
    form.amount = null
    form.currency = settingsStore.currency
    form.category_id = null
    form.transaction_date = new Date()
    form.description = ''
    form.notes = ''
    form.tags = []
  }
  dialogVisible.value = true
}

function addTag() {
  const tag = newTag.value.trim()
  if (tag && !form.tags.includes(tag)) {
    form.tags.push(tag)
  }
  newTag.value = ''
}

function removeTag(tag) {
  form.tags = form.tags.filter(t => t !== tag)
}

async function handleSave() {
  if (!form.category_id) {
    toast.add({ severity: 'warn', summary: 'Please select a category', life: 3000 })
    return
  }
  if (!form.amount || form.amount <= 0) {
    toast.add({ severity: 'warn', summary: 'Amount must be greater than 0', life: 3000 })
    return
  }

  saving.value = true
  try {
    const payload = {
      type: form.type,
      amount: form.amount,
      currency: form.currency,
      category_id: form.category_id,
      transaction_date: toISODate(form.transaction_date),
      description: form.description,
      notes: form.notes,
      tags: form.tags,
    }

    if (editingTxn.value) {
      await txnStore.updateTransaction(editingTxn.value.id, payload)
      toast.add({ severity: 'success', summary: 'Transaction updated', life: 3000 })
    } else {
      await txnStore.createTransaction(payload)
      toast.add({ severity: 'success', summary: 'Transaction created', life: 3000 })
    }
    dialogVisible.value = false
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error saving transaction', life: 5000 })
  } finally {
    saving.value = false
  }
}

function handleDelete(txn) {
  confirm.require({
    message: `Delete this ${txn.type} of ${formatAmount(txn.amount, txn.currency)}?`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await txnStore.deleteTransaction(txn.id)
        toast.add({ severity: 'success', summary: 'Transaction deleted', life: 3000 })
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

// CSV Export
async function handleExport() {
  exporting.value = true
  try {
    const params = {}
    if (txnStore.filters.type) params.type = txnStore.filters.type
    if (txnStore.filters.date_from) params.date_from = txnStore.filters.date_from
    if (txnStore.filters.date_to) params.date_to = txnStore.filters.date_to

    const response = await reportService.exportCsv(params)
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.download = `fintrack_export_${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
    toast.add({ severity: 'success', summary: 'CSV exported', life: 3000 })
  } catch (e) {
    toast.add({ severity: 'error', summary: 'Export failed', life: 5000 })
  } finally {
    exporting.value = false
  }
}

function triggerImport() {
  importInput.value?.click()
}

async function handleImport(event) {
  const file = event.target.files?.[0]
  if (!file) return

  try {
    const { data } = await reportService.importCsv(file)
    if (data.status === 'success') {
      toast.add({ severity: 'success', summary: data.message || `Imported ${data.data.imported} transactions`, life: 5000 })
      txnStore.fetchTransactions(1)
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Import failed', life: 5000 })
  }
  event.target.value = ''
}

// Receipts
async function fetchReceipts(txnId) {
  try {
    const { data } = await receiptService.getByTransaction(txnId)
    if (data.status === 'success') {
      receipts.value = data.data || []
    }
  } catch {
    receipts.value = []
  }
}

async function uploadReceipt(event) {
  const file = event.target.files?.[0]
  if (!file || !editingTxn.value) return

  uploadingReceipt.value = true
  try {
    await receiptService.upload(editingTxn.value.id, file)
    toast.add({ severity: 'success', summary: 'Receipt uploaded', life: 3000 })
    await fetchReceipts(editingTxn.value.id)
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Upload failed', life: 5000 })
  } finally {
    uploadingReceipt.value = false
  }
  event.target.value = ''
}

async function deleteReceipt(receiptId) {
  try {
    await receiptService.delete(receiptId)
    toast.add({ severity: 'success', summary: 'Receipt removed', life: 3000 })
    if (editingTxn.value) await fetchReceipts(editingTxn.value.id)
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  }
}

onMounted(() => {
  txnStore.fetchTransactions()
  categoryStore.fetchCategories()
})
</script>
