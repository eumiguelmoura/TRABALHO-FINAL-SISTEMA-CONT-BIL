import { defineRouter } from '#q-app'
import { routes, handleHotUpdate } from 'vue-router/auto-routes'
import { createMemoryHistory, createRouter, createWebHashHistory, createWebHistory } from 'vue-router'
import { LocalStorage } from 'quasar'
export default defineRouter(() => {
  const history = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory
  const router = createRouter({ routes, history: history(import.meta.env.QUASAR_VUE_ROUTER_BASE) })
  router.beforeEach(to => {
    const authenticated = !!LocalStorage.getItem('auth_data')?.token
    if (to.path === '/') return authenticated ? '/admin' : '/login'
    if (!authenticated && to.path !== '/login') return '/login'
    if (authenticated && to.path === '/login') return '/admin'
    return true
  })
  if (import.meta.hot) handleHotUpdate(router)
  return router
})
