<template>
  <div>
    <h2 class="text-2xl font-bold text-[var(--color-surface-900)] mb-6">Settings</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Preferences -->
      <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-[var(--color-surface-900)] mb-4">
          <i class="pi pi-cog mr-2 text-[var(--color-primary-400)]" />
          Preferences
        </h3>

        <div class="space-y-5">
          <!-- Currency -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Default Currency</label>
            <Select
              v-model="settingsForm.currency"
              :options="settingsStore.currencies"
              optionLabel="name"
              optionValue="code"
              class="w-full"
            >
              <template #value="{ value }">
                <span>{{ getCurrencyLabel(value) }}</span>
              </template>
              <template #option="{ option }">
                <span>{{ option.symbol }} {{ option.name }} ({{ option.code }})</span>
              </template>
            </Select>
          </div>

          <!-- Theme -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Theme</label>
            <div class="flex gap-3">
              <button
                v-for="opt in themeOptions"
                :key="opt.value"
                type="button"
                @click="settingsForm.theme = opt.value"
                class="flex-1 py-3 px-4 rounded-xl border-2 text-sm font-medium transition-all text-center"
                :class="settingsForm.theme === opt.value
                  ? 'border-[var(--color-primary-500)] bg-[var(--color-primary-500)]/10 text-[var(--color-primary-400)]'
                  : 'border-[var(--color-surface-300)] text-[var(--color-surface-500)]'"
              >
                <i :class="opt.icon" class="block text-lg mb-1" />
                {{ opt.label }}
              </button>
            </div>
          </div>

          <!-- Date Format -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Date Format</label>
            <Select
              v-model="settingsForm.date_format"
              :options="dateFormatOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>

          <!-- Language -->
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Language</label>
            <Select
              v-model="settingsForm.language"
              :options="languageOptions"
              optionLabel="label"
              optionValue="value"
              class="w-full"
            />
          </div>

          <Button
            label="Save Preferences"
            icon="pi pi-check"
            @click="saveSettings"
            :loading="savingSettings"
            class="w-full"
          />
        </div>
      </div>

      <!-- Change Password -->
      <div class="glass-card p-6">
        <h3 class="text-lg font-semibold text-[var(--color-surface-900)] mb-4">
          <i class="pi pi-lock mr-2 text-[var(--color-primary-400)]" />
          Change Password
        </h3>

        <form @submit.prevent="changePassword" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Current Password</label>
            <Password
              v-model="passwordForm.current_password"
              :feedback="false"
              toggleMask
              class="w-full"
              inputClass="w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">New Password</label>
            <Password
              v-model="passwordForm.new_password"
              toggleMask
              class="w-full"
              inputClass="w-full"
            />
            <p class="text-xs text-[var(--color-surface-500)] mt-1">Minimum 8 characters</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Confirm New Password</label>
            <Password
              v-model="passwordForm.confirm_password"
              :feedback="false"
              toggleMask
              class="w-full"
              inputClass="w-full"
            />
          </div>

          <Button
            type="submit"
            label="Update Password"
            icon="pi pi-lock"
            :loading="savingPassword"
            severity="secondary"
            class="w-full"
          />
        </form>

        <!-- Account Info (editable) -->
        <div class="mt-6 pt-5 border-t border-[var(--color-surface-200)]">
          <h4 class="text-sm font-semibold text-[var(--color-surface-700)] mb-3">Account</h4>
          <form @submit.prevent="updateProfile" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Name</label>
              <InputText v-model="profileForm.name" class="w-full" placeholder="Your display name" />
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--color-surface-700)] mb-1">Email</label>
              <InputText v-model="profileForm.email" class="w-full" type="email" placeholder="your@email.com" />
            </div>
            <div class="flex justify-between items-center pt-1">
              <span class="text-xs text-[var(--color-surface-500)] capitalize">Role: {{ authStore.user?.role }}</span>
              <Button type="submit" label="Update Profile" size="small" :loading="savingProfile" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useSettingsStore } from '@/stores/settings'
import { useAuthStore } from '@/stores/auth'
import authService from '@/services/authService'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Select from 'primevue/select'
import Password from 'primevue/password'
import InputText from 'primevue/inputtext'

const settingsStore = useSettingsStore()
const authStore = useAuthStore()
const toast = useToast()

const savingSettings = ref(false)
const savingPassword = ref(false)
const savingProfile = ref(false)

const profileForm = reactive({
  name:  authStore.user?.name  || '',
  email: authStore.user?.email || '',
})

const settingsForm = reactive({
  currency: settingsStore.settings.currency,
  theme: settingsStore.settings.theme,
  date_format: settingsStore.settings.date_format,
  language: settingsStore.settings.language,
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: '',
})

const themeOptions = [
  { label: 'Dark', value: 'dark', icon: 'pi pi-moon' },
  { label: 'Light', value: 'light', icon: 'pi pi-sun' },
]

const dateFormatOptions = [
  { label: 'DD/MM/YYYY', value: 'DD/MM/YYYY' },
  { label: 'MM/DD/YYYY', value: 'MM/DD/YYYY' },
  { label: 'YYYY-MM-DD', value: 'YYYY-MM-DD' },
]

const languageOptions = [
  { label: 'English', value: 'en' },
]

function getCurrencyLabel(code) {
  const c = settingsStore.currencies.find(c => c.code === code)
  return c ? `${c.symbol} ${c.name}` : code
}

async function updateProfile() {
  if (!profileForm.name.trim() || profileForm.name.trim().length < 2) {
    toast.add({ severity: 'warn', summary: 'Name must be at least 2 characters', life: 3000 })
    return
  }
  if (!profileForm.email.trim()) {
    toast.add({ severity: 'warn', summary: 'Email is required', life: 3000 })
    return
  }

  savingProfile.value = true
  try {
    const { data } = await authService.updateProfile(profileForm.name.trim(), profileForm.email.trim())
    if (data.status === 'success') {
      await authStore.fetchUserData()
      toast.add({ severity: 'success', summary: 'Profile updated', life: 3000 })
    }
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error updating profile', life: 5000 })
  } finally {
    savingProfile.value = false
  }
}

async function saveSettings() {
  savingSettings.value = true
  try {
    await settingsStore.updateSettings(settingsForm)
    toast.add({ severity: 'success', summary: 'Settings saved', life: 3000 })
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error saving settings', life: 5000 })
  } finally {
    savingSettings.value = false
  }
}

async function changePassword() {
  if (!passwordForm.current_password || !passwordForm.new_password) {
    toast.add({ severity: 'warn', summary: 'Both password fields are required', life: 3000 })
    return
  }
  if (passwordForm.new_password.length < 8) {
    toast.add({ severity: 'warn', summary: 'New password must be at least 8 characters', life: 3000 })
    return
  }
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    toast.add({ severity: 'warn', summary: 'Passwords do not match', life: 3000 })
    return
  }

  savingPassword.value = true
  try {
    await authService.changePassword(passwordForm.current_password, passwordForm.new_password)
    toast.add({ severity: 'success', summary: 'Password changed successfully', life: 3000 })
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.confirm_password = ''
  } catch (e) {
    toast.add({ severity: 'error', summary: e.response?.data?.message || 'Error changing password', life: 5000 })
  } finally {
    savingPassword.value = false
  }
}

onMounted(() => {
  settingsStore.fetchCurrencies()
})
</script>
