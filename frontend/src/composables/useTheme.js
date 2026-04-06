import { ref, watch } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'dark')

export function useTheme() {
  function applyTheme(t) {
    if (t === 'light') {
      document.documentElement.classList.add('light')
    } else {
      document.documentElement.classList.remove('light')
    }
  }

  function toggleTheme() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  function setTheme(t) {
    theme.value = t
  }

  watch(theme, (val) => {
    localStorage.setItem('theme', val)
    applyTheme(val)
  }, { immediate: true })

  return {
    theme,
    toggleTheme,
    setTheme,
    isDark: () => theme.value === 'dark',
  }
}
