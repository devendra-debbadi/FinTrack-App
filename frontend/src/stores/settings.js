import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useTheme } from '@/composables/useTheme'

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref({
    currency: 'EUR',
    theme: 'dark',
    language: 'en',
    date_format: 'DD/MM/YYYY',
  })

  const currencies = ref([])

  const currency = computed(() => settings.value.currency)
  const dateFormat = computed(() => settings.value.date_format)

  function setSettings(data) {
    settings.value = { ...settings.value, ...data }

    // Apply theme
    const { setTheme } = useTheme()
    if (data.theme) {
      setTheme(data.theme)
    }
  }

  async function fetchSettings() {
    const { data } = await api.get('/settings')
    if (data.status === 'success') {
      setSettings(data.data)
    }
  }

  async function updateSettings(updates) {
    const { data } = await api.put('/settings', updates)
    if (data.status === 'success') {
      setSettings(data.data)
    }
    return data
  }

  async function fetchCurrencies() {
    const { data } = await api.get('/settings/currencies')
    if (data.status === 'success') {
      currencies.value = data.data
    }
  }

  return {
    settings,
    currencies,
    currency,
    dateFormat,
    setSettings,
    fetchSettings,
    updateSettings,
    fetchCurrencies,
  }
})
