import { createRouter, createWebHistory } from 'vue-router'
import Login from "../components/Login.vue";
import Register from "@/components/Register.vue";
import Chat from "@/components/Chat.vue";

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name:'login' ,component: Login },
  { path: '/register',name:'register', component: Register },
  { path: '/chat', name: 'chat', component:  Chat, meta: { requiresAuth: true }}
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
