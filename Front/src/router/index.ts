import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: HomeView },
    { path: '/about', component: () => import('../views/AboutView.vue') },
    { path: '/servicios', component: () => import('../components/Servicios.vue') },
    { path: '/categorias', component: () => import('../components/Categorias.vue') },
    { path: '/clientes', component: () => import('../components/Clientes.vue') },
    

  ]
})

export default router
