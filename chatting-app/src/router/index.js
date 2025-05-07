import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name:'login' ,component: () => import('../components/Login.vue') },
  { path: '/register',name:'register', component: () => import('../components/Register.vue') },
  { path: '/chat', name: 'chat', component: () => import('../components/Chat.vue'), meta: { requiresAuth: true }}
];
const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token')
  if (to.meta.requiresAuth  && !isAuthenticated) {
    next('/login');
  } else {
    next()
  }
});


export default router
