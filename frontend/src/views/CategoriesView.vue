<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Categories</h2>
      <Button label="Add Category" icon="pi pi-plus" @click="openDialog()" severity="primary" />
    </div>

    <!-- Tabs: Expense / Income / Archived -->
    <Tabs v-model:value="activeTab" class="mb-4">
      <TabList>
        <Tab value="expense">Expense ({{ categoryStore.expenseCategories.length }})</Tab>
        <Tab value="income">Income ({{ categoryStore.incomeCategories.length }})</Tab>
        <Tab value="archived">Archived ({{ categoryStore.archivedCategories.length }})</Tab>
      </TabList>
    </Tabs>

    <!-- Category grid -->
    <div v-if="categoryStore.loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="cat in displayedCategories"
        :key="cat.id"
        class="glass-card p-4 flex items-center gap-4 group"
        :class="{ 'opacity-50': Number(cat.is_archived) === 1 }"
      >
        <!-- Icon -->
        <div
          class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
          :style="{ backgroundColor: cat.color + '20', color: cat.color }"
        >
          <i :class="cat.icon" class="text-xl" />
        </div>

        <!-- Name + type -->
        <div class="flex-1 min-w-0">
          <p class="font-medium text-[var(--color-surface-900)] truncate">{{ cat.name }}</p>
          <p class="text-xs text-[var(--color-surface-500)] capitalize">{{ cat.type }}</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            @click="openDialog(cat)"
            class="p-2 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]"
            title="Edit"
          >
            <i class="pi pi-pencil text-sm" />
          </button>
          <button
            @click="handleArchive(cat)"
            class="p-2 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]"
            :title="Number(cat.is_archived) === 1 ? 'Restore' : 'Archive'"
          >
            <i :class="Number(cat.is_archived) === 1 ? 'pi pi-replay' : 'pi pi-inbox'" class="text-sm" />
          </button>
          <button
            v-if="Number(cat.is_archived) === 1"
            @click="handleDelete(cat)"
            class="p-2 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]"
            title="Delete"
          >
            <i class="pi pi-trash text-sm" />
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="displayedCategories.length === 0" class="col-span-full text-center py-12">
        <i class="pi pi-tags text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">No {{ activeTab }} categories yet</p>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editingCategory ? 'Edit Category' : 'New Category'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="form.name" class="w-full" placeholder="Category name" />
          </div>

          <!-- Type -->
          <div v-if="!editingCategory">
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Type</label>
            <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" />
          </div>

          <!-- Icon -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Icon</label>
            <Select v-model="form.icon" :options="iconOptions" optionLabel="label" optionValue="value" class="w-full">
              <template #value="{ value }">
                <div class="flex items-center gap-2">
                  <i :class="value" />
                  <span>{{ value }}</span>
                </div>
              </template>
              <template #option="{ option }">
                <div class="flex items-center gap-2">
                  <i :class="option.value" />
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
                @click="form.color = c"
                class="w-8 h-8 rounded-lg border-2 transition-all"
                :style="{ backgroundColor: c }"
                :class="form.color === c ? 'border-white scale-110' : 'border-transparent'"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="dialogVisible = false" />
          <Button type="submit" :label="editingCategory ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useCategoryStore } from '@/stores/categories'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import ProgressSpinner from 'primevue/progressspinner'

const categoryStore = useCategoryStore()
const toast = useToast()
const confirm = useConfirm()

const activeTab = ref('expense')
const dialogVisible = ref(false)
const editingCategory = ref(null)
const saving = ref(false)

const form = reactive({
  name: '',
  type: 'expense',
  icon: 'pi-tag',
  color: '#6366f1',
})

const displayedCategories = computed(() => {
  if (activeTab.value === 'archived') return categoryStore.archivedCategories
  if (activeTab.value === 'income') return categoryStore.incomeCategories
  return categoryStore.expenseCategories
})

const typeOptions = [
  { label: 'Expense', value: 'expense' },
  { label: 'Income', value: 'income' },
]

const iconOptions = [
  { label: 'Home', value: 'pi pi-home' },
  { label: 'Car', value: 'pi pi-car' },
  { label: 'Shopping Bag', value: 'pi pi-shopping-bag' },
  { label: 'Shopping Cart', value: 'pi pi-shopping-cart' },
  { label: 'Bolt', value: 'pi pi-bolt' },
  { label: 'Heart', value: 'pi pi-heart' },
  { label: 'Shield', value: 'pi pi-shield' },
  { label: 'Ticket', value: 'pi pi-ticket' },
  { label: 'Tag', value: 'pi pi-tag' },
  { label: 'Book', value: 'pi pi-book' },
  { label: 'User', value: 'pi pi-user' },
  { label: 'Globe', value: 'pi pi-globe' },
  { label: 'Gift', value: 'pi pi-gift' },
  { label: 'Wallet', value: 'pi pi-wallet' },
  { label: 'Briefcase', value: 'pi pi-briefcase' },
  { label: 'Chart', value: 'pi pi-chart-line' },
  { label: 'Building', value: 'pi pi-building' },
  { label: 'Star', value: 'pi pi-star' },
  { label: 'Replay', value: 'pi pi-replay' },
  { label: 'Other', value: 'pi pi-ellipsis-h' },
]

const colorOptions = [
  '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
  '#f43f5e', '#ef4444', '#f97316', '#f59e0b', '#84cc16',
  '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
  '#3b82f6', '#6b7280',
]

function openDialog(cat = null) {
  editingCategory.value = cat
  if (cat) {
    form.name = cat.name
    form.type = cat.type
    form.icon = cat.icon
    form.color = cat.color
  } else {
    form.name = ''
    form.type = activeTab.value === 'income' ? 'income' : 'expense'
    form.icon = 'pi pi-tag'
    form.color = '#6366f1'
  }
  dialogVisible.value = true
}

async function handleSave() {
  if (!form.name.trim()) {
    toast.add({ severity: 'warn', summary: 'Name required', life: 3000 })
    return
  }

  saving.value = true
  try {
    if (editingCategory.value) {
      await categoryStore.updateCategory(editingCategory.value.id, {
        name: form.name,
        icon: form.icon,
        color: form.color,
      })
      toast.add({ severity: 'success', summary: 'Category updated', life: 3000 })
    } else {
      await categoryStore.createCategory(form)
      toast.add({ severity: 'success', summary: 'Category created', life: 3000 })
    }
    dialogVisible.value = false
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

async function handleArchive(cat) {
  try {
    const data = await categoryStore.toggleArchive(cat.id)
    toast.add({ severity: 'success', summary: data.message, life: 3000 })
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  }
}

function handleDelete(cat) {
  confirm.require({
    message: `Delete "${cat.name}"? This cannot be undone.`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        const data = await categoryStore.deleteCategory(cat.id)
        toast.add({ severity: 'success', summary: data.message, life: 3000 })
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(() => categoryStore.fetchCategories())
</script>
