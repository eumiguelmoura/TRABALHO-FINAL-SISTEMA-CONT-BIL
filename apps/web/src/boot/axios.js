import { defineBoot } from '#q-app'
import axios from 'axios'
import { LocalStorage, Notify } from 'quasar'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8182/api',
  timeout: 20000,
  headers: { Accept: 'application/json' },
})
api.interceptors.request.use(config => {
  const token = LocalStorage.getItem('auth_data')?.token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})
api.interceptors.response.use(response => response, error => {
  if (error.response?.status === 401 && !error.config?.url?.includes('auth/login')) {
    LocalStorage.remove('auth_data')
    window.location.hash = '#/login'
  }
  if (!error.response) Notify.create({ type: 'negative', message: 'Não foi possível conectar à API. Verifique o servidor Laravel.' })
  return Promise.reject(error)
})
export default defineBoot(({ app }) => {
  app.config.globalProperties.$api = api
})
export { api }
