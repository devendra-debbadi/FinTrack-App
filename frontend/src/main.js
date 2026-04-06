import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import router from './router'
import App from './App.vue'
import './assets/css/main.css'

const app = createApp(App)

// Pinia (state management)
app.use(createPinia())

// Vue Router
app.use(router)

// PrimeVue with Aura theme
app.use(PrimeVue, {
  theme: {
    preset: Aura,
    options: {
      prefix: 'p',
      darkModeSelector: ':root:not(.light)',
      cssLayer: false,
    },
  },
  ripple: true,
})

// PrimeVue services
app.use(ToastService)
app.use(ConfirmationService)

app.mount('#app')
