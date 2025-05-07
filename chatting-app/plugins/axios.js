import axios from 'axios'
import store from '@/store'

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
  },
})

instance.interceptors.request.use(config => {
  const token = store.state.token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default instance
