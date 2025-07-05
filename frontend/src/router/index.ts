// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/home.vue';
import UserProfileWrapper from '../components/profil/UserProfileWrapper.vue';

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/profile', name: 'Profile', component: UserProfileWrapper }, // ✅ cette ligne
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
