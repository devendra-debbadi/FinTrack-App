<template>
  <div>
    <h2 class="text-2xl font-bold text-[var(--color-surface-900)] mb-6">Admin Panel</h2>

    <!-- Tab switcher -->
    <div class="flex gap-1 mb-6 p-1 bg-[var(--color-surface-100)] rounded-xl w-fit">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="activeTab === tab.key
          ? 'bg-[var(--color-primary-500)] text-white'
          : 'text-[var(--color-surface-500)] hover:text-[var(--color-surface-300)]'"
      >
        <i :class="tab.icon" class="mr-1.5" />{{ tab.label }}
      </button>
    </div>

    <!-- ===== USER MANAGEMENT TAB ===== -->
    <template v-if="activeTab === 'users'">
      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="glass-card p-4" v-for="stat in statCards" :key="stat.label">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-[var(--color-surface-500)]">{{ stat.label }}</span>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-[var(--color-primary-500)]/15">
              <i :class="stat.icon" class="text-[var(--color-primary-400)] text-sm" />
            </div>
          </div>
          <p class="text-2xl font-bold text-[var(--color-surface-900)]">{{ stat.value }}</p>
        </div>
      </div>

      <!-- User Management -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[var(--color-surface-300)]">User Management</h3>
        <Button label="Create User" icon="pi pi-user-plus" @click="openCreateDialog" severity="primary" size="small" />
      </div>

      <div class="glass-card overflow-hidden">
        <div v-if="loading" class="flex justify-center py-12">
          <ProgressSpinner strokeWidth="3" />
        </div>

        <DataTable v-else :value="users" stripedRows :rowHover="true" class="p-datatable-sm">
          <template #empty>
            <div class="text-center py-8 text-[var(--color-surface-500)]">No users found</div>
          </template>

          <Column header="User" style="min-width: 200px">
            <template #body="{ data }">
              <div class="flex items-center gap-3">
                <div
                  class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                  :class="Number(data.is_active) === 1 ? 'bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)]' : 'bg-[var(--color-surface-700)] text-[var(--color-surface-500)]'"
                >
                  {{ data.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="text-sm font-medium text-[var(--color-surface-300)]">{{ data.name }}</p>
                  <p class="text-xs text-[var(--color-surface-500)]">{{ data.email }}</p>
                </div>
              </div>
            </template>
          </Column>

          <Column header="Role" style="min-width: 90px">
            <template #body="{ data }">
              <Tag
                :value="data.role"
                :severity="data.role === 'admin' ? 'warn' : 'info'"
                class="capitalize text-xs"
              />
            </template>
          </Column>

          <Column header="Status" style="min-width: 90px">
            <template #body="{ data }">
              <Tag
                :value="Number(data.is_active) === 1 ? 'Active' : 'Inactive'"
                :severity="Number(data.is_active) === 1 ? 'success' : 'danger'"
                class="text-xs"
              />
            </template>
          </Column>

          <Column header="Created" style="min-width: 120px">
            <template #body="{ data }">
              <span class="text-xs text-[var(--color-surface-500)]">{{ formatDate(data.created_at) }}</span>
            </template>
          </Column>

          <Column header="Actions" style="width: 180px">
            <template #body="{ data }">
              <div class="flex gap-1">
                <Button
                  icon="pi pi-pencil"
                  title="Edit User"
                  severity="secondary"
                  text
                  size="small"
                  @click="openEditDialog(data)"
                />
                <Button
                  :icon="Number(data.is_active) === 1 ? 'pi pi-ban' : 'pi pi-check'"
                  :title="Number(data.is_active) === 1 ? 'Deactivate' : 'Activate'"
                  severity="secondary"
                  text
                  size="small"
                  @click="toggleUser(data)"
                />
                <Button
                  icon="pi pi-key"
                  title="Reset Password"
                  severity="secondary"
                  text
                  size="small"
                  @click="openResetDialog(data)"
                />
                <Button
                  v-if="data.id !== currentUserId"
                  icon="pi pi-trash"
                  title="Delete User"
                  severity="danger"
                  text
                  size="small"
                  @click="confirmDelete(data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </template>

    <!-- ===== ACTIVITY LOGS TAB ===== -->
    <template v-if="activeTab === 'logs'">
      <div class="flex flex-wrap items-end gap-3 mb-4">
        <h3 class="text-lg font-semibold text-[var(--color-surface-300)] mr-auto">Activity Logs</h3>

        <!-- Filters -->
        <Select
          v-model="logFilters.user_id"
          :options="[{ label: 'All Users', value: '' }, ...userOptions]"
          optionLabel="label"
          optionValue="value"
          placeholder="All Users"
          class="w-44"
          size="small"
          @change="fetchLogs(1)"
        />
        <Select
          v-model="logFilters.action"
          :options="actionOptions"
          optionLabel="label"
          optionValue="value"
          placeholder="All Actions"
          class="w-48"
          size="small"
          @change="fetchLogs(1)"
        />
        <InputText
          v-model="logFilters.date_from"
          type="date"
          class="w-38"
          size="small"
          @change="fetchLogs(1)"
        />
        <InputText
          v-model="logFilters.date_to"
          type="date"
          class="w-38"
          size="small"
          @change="fetchLogs(1)"
        />
        <Button icon="pi pi-filter-slash" severity="secondary" text size="small" title="Clear filters" @click="clearLogFilters" />
      </div>

      <div class="glass-card overflow-hidden">
        <div v-if="logsLoading" class="flex justify-center py-12">
          <ProgressSpinner strokeWidth="3" />
        </div>

        <DataTable v-else :value="logs" stripedRows :rowHover="true" class="p-datatable-sm">
          <template #empty>
            <div class="text-center py-8 text-[var(--color-surface-500)]">No activity logs found</div>
          </template>

          <Column header="Time" style="min-width: 140px">
            <template #body="{ data }">
              <span class="text-xs text-[var(--color-surface-500)]">{{ formatDateTime(data.created_at) }}</span>
            </template>
          </Column>

          <Column header="User" style="min-width: 160px">
            <template #body="{ data }">
              <div v-if="data.user_name">
                <p class="text-sm text-[var(--color-surface-300)]">{{ data.user_name }}</p>
                <p class="text-xs text-[var(--color-surface-500)]">{{ data.user_email }}</p>
              </div>
              <span v-else class="text-xs text-[var(--color-surface-500)]">—</span>
            </template>
          </Column>

          <Column header="Action" style="min-width: 150px">
            <template #body="{ data }">
              <Tag :value="data.action" :severity="actionSeverity(data.action)" class="text-xs font-mono" />
            </template>
          </Column>

          <Column header="Entity" style="min-width: 100px">
            <template #body="{ data }">
              <span v-if="data.entity_type" class="text-xs text-[var(--color-surface-400)]">
                {{ data.entity_type }} #{{ data.entity_id }}
              </span>
              <span v-else class="text-xs text-[var(--color-surface-600)]">—</span>
            </template>
          </Column>

          <Column header="Description" style="min-width: 200px">
            <template #body="{ data }">
              <span class="text-xs text-[var(--color-surface-400)]">{{ data.description || '—' }}</span>
            </template>
          </Column>

          <Column header="IP" style="min-width: 110px">
            <template #body="{ data }">
              <span class="text-xs text-[var(--color-surface-500)] font-mono">{{ data.ip_address || '—' }}</span>
            </template>
          </Column>
        </DataTable>

        <!-- Pagination -->
        <div v-if="logsMeta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-[var(--color-surface-200)]">
          <span class="text-xs text-[var(--color-surface-500)]">
            Page {{ logsMeta.page }} of {{ logsMeta.last_page }} · {{ logsMeta.total }} records
          </span>
          <div class="flex gap-2">
            <Button
              icon="pi pi-chevron-left"
              severity="secondary"
              text
              size="small"
              :disabled="logsMeta.page <= 1"
              @click="fetchLogs(logsMeta.page - 1)"
            />
            <Button
              icon="pi pi-chevron-right"
              severity="secondary"
              text
              size="small"
              :disabled="logsMeta.page >= logsMeta.last_page"
              @click="fetchLogs(logsMeta.page + 1)"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- Create User Dialog -->
    <Dialog v-model:visible="createDialogVisible" header="Create User" modal class="w-full max-w-md">
      <form @submit.prevent="handleCreate">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="createForm.name" class="w-full" placeholder="Full name" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Email</label>
            <InputText v-model="createForm.email" class="w-full" type="email" placeholder="email@example.com" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Password</label>
            <Password v-model="createForm.password" toggleMask class="w-full" inputClass="w-full" />
            <p class="text-xs text-[var(--color-surface-500)] mt-1">Min 8 characters. User must change on first login.</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Role</label>
            <Select v-model="createForm.role" :options="roleOptions" optionLabel="label" optionValue="value" class="w-full" />
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="createDialogVisible = false" />
          <Button type="submit" label="Create" :loading="saving" />
        </div>
      </form>
    </Dialog>

    <!-- Edit User Dialog -->
    <Dialog v-model:visible="editDialogVisible" header="Edit User" modal class="w-full max-w-md">
      <form @submit.prevent="handleEdit">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="editForm.name" class="w-full" placeholder="Full name" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Email</label>
            <InputText v-model="editForm.email" class="w-full" type="email" placeholder="email@example.com" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Role</label>
            <Select v-model="editForm.role" :options="roleOptions" optionLabel="label" optionValue="value" class="w-full" />
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="editDialogVisible = false" />
          <Button type="submit" label="Save Changes" :loading="editing" />
        </div>
      </form>
    </Dialog>

    <!-- Reset Password Dialog -->
    <Dialog v-model:visible="resetDialogVisible" header="Reset Password" modal class="w-full max-w-sm">
      <form @submit.prevent="handleResetPassword">
        <p class="text-sm text-[var(--color-surface-500)] mb-4">
          Reset password for <strong class="text-[var(--color-surface-300)]">{{ resetUser?.name }}</strong>
        </p>
        <div>
          <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">New Password</label>
          <Password v-model="resetPassword" toggleMask class="w-full" inputClass="w-full" />
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="resetDialogVisible = false" />
          <Button type="submit" label="Reset" severity="danger" :loading="resetting" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import ProgressSpinner from 'primevue/progressspinner'

const toast = useToast()
const confirm = useConfirm()
const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id)

// ── Tabs ─────────────────────────────────────────────────────────────────────
const activeTab = ref('users')
const tabs = [
  { key: 'users', label: 'Users', icon: 'pi pi-users' },
  { key: 'logs',  label: 'Activity Logs', icon: 'pi pi-list' },
]

// ── User management ───────────────────────────────────────────────────────────
const users = ref([])
const stats = ref({ total_users: 0, active_users: 0, total_transactions: 0, total_categories: 0 })
const loading = ref(false)
const saving = ref(false)
const editing = ref(false)
const resetting = ref(false)
const createDialogVisible = ref(false)
const editDialogVisible = ref(false)
const resetDialogVisible = ref(false)
const editingUser = ref(null)
const resetUser = ref(null)
const resetPassword = ref('')

const editForm = reactive({ name: '', email: '', role: 'user' })

const createForm = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user',
})

const roleOptions = [
  { label: 'User', value: 'user' },
  { label: 'Admin', value: 'admin' },
]

const statCards = computed(() => [
  { label: 'Total Users', value: stats.value.total_users, icon: 'pi pi-users' },
  { label: 'Active Users', value: stats.value.active_users, icon: 'pi pi-user-check' },
  { label: 'Transactions', value: stats.value.total_transactions, icon: 'pi pi-receipt' },
  { label: 'Categories', value: stats.value.total_categories, icon: 'pi pi-tags' },
])

const userOptions = computed(() =>
  users.value.map(u => ({ label: `${u.name} (${u.email})`, value: String(u.id) }))
)

// ── Activity logs ─────────────────────────────────────────────────────────────
const logs = ref([])
const logsLoading = ref(false)
const logsMeta = ref({ page: 1, last_page: 1, total: 0, per_page: 50 })
const logFilters = reactive({ user_id: '', action: '', date_from: '', date_to: '' })

const actionOptions = [
  { label: 'All Actions', value: '' },
  { label: 'login', value: 'login' },
  { label: 'logout', value: 'logout' },
  { label: 'register', value: 'register' },
  { label: 'password_change', value: 'password_change' },
  { label: 'transaction_create', value: 'transaction_create' },
  { label: 'transaction_update', value: 'transaction_update' },
  { label: 'transaction_delete', value: 'transaction_delete' },
  { label: 'category_create', value: 'category_create' },
  { label: 'category_update', value: 'category_update' },
  { label: 'category_archive', value: 'category_archive' },
  { label: 'category_restore', value: 'category_restore' },
  { label: 'category_delete', value: 'category_delete' },
  { label: 'budget_create', value: 'budget_create' },
  { label: 'budget_update', value: 'budget_update' },
  { label: 'budget_delete', value: 'budget_delete' },
  { label: 'goal_create', value: 'goal_create' },
  { label: 'goal_update', value: 'goal_update' },
  { label: 'goal_deposit', value: 'goal_deposit' },
  { label: 'goal_delete', value: 'goal_delete' },
  { label: 'admin_user_create', value: 'admin_user_create' },
  { label: 'admin_user_update', value: 'admin_user_update' },
  { label: 'admin_user_delete', value: 'admin_user_delete' },
  { label: 'admin_user_activate', value: 'admin_user_activate' },
  { label: 'admin_user_deactivate', value: 'admin_user_deactivate' },
  { label: 'admin_password_reset', value: 'admin_password_reset' },
]

function actionSeverity(action) {
  if (action.includes('delete')) return 'danger'
  if (action.includes('create') || action === 'register') return 'success'
  if (action.startsWith('admin_')) return 'warn'
  if (action === 'login' || action === 'logout') return 'info'
  return 'secondary'
}

// ── Shared helpers ────────────────────────────────────────────────────────────
function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

// ── Data fetching ─────────────────────────────────────────────────────────────
async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users')
    if (data.status === 'success') users.value = data.data
  } finally { loading.value = false }
}

async function fetchStats() {
  try {
    const { data } = await api.get('/admin/stats')
    if (data.status === 'success') stats.value = data.data
  } catch { /* non-critical */ }
}

async function fetchLogs(page = 1) {
  logsLoading.value = true
  try {
    const params = { page, per_page: 50 }
    if (logFilters.user_id)  params.user_id  = logFilters.user_id
    if (logFilters.action)   params.action   = logFilters.action
    if (logFilters.date_from) params.date_from = logFilters.date_from
    if (logFilters.date_to)  params.date_to  = logFilters.date_to

    const { data } = await api.get('/admin/activity-logs', { params })
    if (data.status === 'success') {
      logs.value    = data.data.data
      logsMeta.value = {
        page:      data.data.page,
        last_page: data.data.last_page,
        total:     data.data.total,
        per_page:  data.data.per_page,
      }
    }
  } catch { /* non-critical */ }
  finally { logsLoading.value = false }
}

function clearLogFilters() {
  logFilters.user_id   = ''
  logFilters.action    = ''
  logFilters.date_from = ''
  logFilters.date_to   = ''
  fetchLogs(1)
}

// ── User actions ──────────────────────────────────────────────────────────────
function openCreateDialog() {
  createForm.name = ''
  createForm.email = ''
  createForm.password = ''
  createForm.role = 'user'
  createDialogVisible.value = true
}

async function handleCreate() {
  if (!createForm.name || !createForm.email || !createForm.password) {
    toast.add({ severity: 'warn', summary: 'All fields are required', life: 3000 })
    return
  }
  if (createForm.password.length < 8) {
    toast.add({ severity: 'warn', summary: 'Password must be at least 8 characters', life: 3000 })
    return
  }

  saving.value = true
  try {
    const { data } = await api.post('/admin/users', createForm)
    if (data.status === 'success') {
      toast.add({ severity: 'success', summary: 'User created', life: 3000 })
      createDialogVisible.value = false
      await fetchUsers()
      await fetchStats()
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error creating user', life: 5000 })
  } finally { saving.value = false }
}

async function toggleUser(user) {
  try {
    const { data } = await api.patch(`/admin/users/${user.id}/toggle`)
    if (data.status === 'success') {
      toast.add({ severity: 'success', summary: data.message, life: 3000 })
      await fetchUsers()
      await fetchStats()
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  }
}

function openEditDialog(user) {
  editingUser.value = user
  editForm.name = user.name
  editForm.email = user.email
  editForm.role = user.role
  editDialogVisible.value = true
}

async function handleEdit() {
  if (!editForm.name || !editForm.email) {
    toast.add({ severity: 'warn', summary: 'Name and email are required', life: 3000 })
    return
  }

  editing.value = true
  try {
    const { data } = await api.put(`/admin/users/${editingUser.value.id}`, editForm)
    if (data.status === 'success') {
      toast.add({ severity: 'success', summary: 'User updated', life: 3000 })
      editDialogVisible.value = false
      await fetchUsers()
      // If the admin edited their own account, refresh auth store so header updates
      if (Number(editingUser.value.id) === Number(currentUserId.value)) {
        await authStore.fetchUserData()
      }
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error updating user', life: 5000 })
  } finally { editing.value = false }
}

function confirmDelete(user) {
  confirm.require({
    message: `Delete "${user.name}"? All their data will be permanently removed.`,
    header: 'Delete User',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        const { data } = await api.delete(`/admin/users/${user.id}`)
        if (data.status === 'success') {
          toast.add({ severity: 'success', summary: 'User deleted', life: 3000 })
          await fetchUsers()
          await fetchStats()
        }
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error deleting user', life: 5000 })
      }
    },
  })
}

function openResetDialog(user) {
  resetUser.value = user
  resetPassword.value = ''
  resetDialogVisible.value = true
}

async function handleResetPassword() {
  if (!resetPassword.value || resetPassword.value.length < 8) {
    toast.add({ severity: 'warn', summary: 'Password must be at least 8 characters', life: 3000 })
    return
  }

  resetting.value = true
  try {
    const { data } = await api.patch(`/admin/users/${resetUser.value.id}/reset-password`, {
      password: resetPassword.value,
    })
    if (data.status === 'success') {
      toast.add({ severity: 'success', summary: 'Password reset successfully', life: 3000 })
      resetDialogVisible.value = false
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally { resetting.value = false }
}

onMounted(() => {
  fetchUsers()
  fetchStats()
  fetchLogs()
})
</script>
