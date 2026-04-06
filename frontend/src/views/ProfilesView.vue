<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold text-[var(--color-surface-900)]">Profiles</h2>
      <Button label="Add Profile" icon="pi pi-plus" @click="openDialog()" severity="primary" />
    </div>

    <p class="text-[var(--color-surface-500)] mb-6">
      Profiles let you separate finances — e.g. Personal, Business, Freelance.
    </p>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <ProgressSpinner strokeWidth="3" />
    </div>

    <!-- Profile cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="profile in profiles"
        :key="profile.id"
        class="glass-card p-5 flex flex-col gap-3 group relative"
        :class="{ 'ring-2 ring-[var(--color-primary-500)]': profile.is_default }"
      >
        <!-- Default badge -->
        <span
          v-if="profile.is_default"
          class="absolute top-3 right-3 text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)]"
        >
          Default
        </span>

        <!-- Icon + name -->
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[var(--color-primary-500)]/15 text-[var(--color-primary-400)]">
            <i class="pi pi-id-card text-xl" />
          </div>
          <div>
            <p class="font-semibold text-[var(--color-surface-900)]">{{ profile.name }}</p>
            <p class="text-xs text-[var(--color-surface-500)]">
              Created {{ formatDate(profile.created_at) }}
            </p>
          </div>
        </div>

        <!-- Description -->
        <p v-if="profile.description" class="text-sm text-[var(--color-surface-500)] line-clamp-2">
          {{ profile.description }}
        </p>

        <!-- Actions -->
        <div class="flex gap-2 mt-auto pt-2 border-t border-[var(--color-surface-200)]">
          <Button
            v-if="Number(profile.is_default) === 0"
            label="Set Default"
            icon="pi pi-check-circle"
            size="small"
            severity="secondary"
            text
            @click="handleSetDefault(profile)"
          />
          <div class="ml-auto flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button
              @click="openDialog(profile)"
              class="p-2 rounded-lg hover:bg-[var(--color-surface-200)] text-[var(--color-surface-500)]"
              title="Edit"
            >
              <i class="pi pi-pencil text-sm" />
            </button>
            <button
              v-if="Number(profile.is_default) === 0"
              @click="handleDelete(profile)"
              class="p-2 rounded-lg hover:bg-[var(--color-expense)]/10 text-[var(--color-expense)]"
              title="Delete"
            >
              <i class="pi pi-trash text-sm" />
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="profiles.length === 0" class="col-span-full text-center py-12">
        <i class="pi pi-id-card text-4xl text-[var(--color-surface-400)] mb-3" />
        <p class="text-[var(--color-surface-500)]">No profiles yet</p>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editingProfile ? 'Edit Profile' : 'New Profile'"
      modal
      class="w-full max-w-md"
    >
      <form @submit.prevent="handleSave">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
            <InputText v-model="form.name" class="w-full" placeholder="e.g. Personal, Business" />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Description</label>
            <Textarea v-model="form.description" class="w-full" rows="3" placeholder="Optional description" />
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button label="Cancel" severity="secondary" text @click="dialogVisible = false" />
          <Button type="submit" :label="editingProfile ? 'Update' : 'Create'" :loading="saving" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import profileService from '@/services/profileService'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import ProgressSpinner from 'primevue/progressspinner'

const authStore = useAuthStore()
const toast = useToast()
const confirm = useConfirm()

const profiles = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const editingProfile = ref(null)
const saving = ref(false)

const form = reactive({
  name: '',
  description: '',
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function fetchProfiles() {
  loading.value = true
  try {
    const { data } = await profileService.getAll()
    if (data.status === 'success') {
      profiles.value = data.data
    }
  } finally {
    loading.value = false
  }
}

function openDialog(profile = null) {
  editingProfile.value = profile
  if (profile) {
    form.name = profile.name
    form.description = profile.description || ''
  } else {
    form.name = ''
    form.description = ''
  }
  dialogVisible.value = true
}

async function handleSave() {
  if (!form.name.trim() || form.name.trim().length < 2) {
    toast.add({ severity: 'warn', summary: 'Name must be at least 2 characters', life: 3000 })
    return
  }

  saving.value = true
  try {
    if (editingProfile.value) {
      const { data } = await profileService.update(editingProfile.value.id, {
        name: form.name,
        description: form.description,
      })
      if (data.status === 'success') {
        await fetchProfiles()
        toast.add({ severity: 'success', summary: 'Profile updated', life: 3000 })
      }
    } else {
      const { data } = await profileService.create({
        name: form.name,
        description: form.description,
      })
      if (data.status === 'success') {
        await fetchProfiles()
        toast.add({ severity: 'success', summary: 'Profile created', life: 3000 })
      }
    }
    dialogVisible.value = false
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  } finally {
    saving.value = false
  }
}

async function handleSetDefault(profile) {
  try {
    const { data } = await profileService.setDefault(profile.id)
    if (data.status === 'success') {
      authStore.setActiveProfile(profile.id)
      await fetchProfiles()
      toast.add({ severity: 'success', summary: `"${profile.name}" is now your default profile`, life: 3000 })
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
  }
}

function handleDelete(profile) {
  confirm.require({
    message: `Delete "${profile.name}"? All data in this profile will be lost.`,
    header: 'Confirm Delete',
    icon: 'pi pi-trash',
    rejectLabel: 'Cancel',
    acceptLabel: 'Delete',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        const { data } = await profileService.delete(profile.id)
        if (data.status === 'success') {
          await fetchProfiles()
          toast.add({ severity: 'success', summary: 'Profile deleted', life: 3000 })
        }
      } catch (e) {
        toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error', life: 5000 })
      }
    },
  })
}

onMounted(() => fetchProfiles())
</script>
