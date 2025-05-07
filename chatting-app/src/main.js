import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import store from "./store";
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

console.log('VITE_API_URL:', import.meta.env.VITE_API_URL);

const app = createApp(App)
app.use(store)
app.use(router)
app.mount('#app')

