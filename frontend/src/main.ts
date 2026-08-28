import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './app/App.vue'
import { router } from './app/router'
import './app/styles.css'

localStorage.removeItem('token')

createApp(App).use(createPinia()).use(router).mount('#app')
