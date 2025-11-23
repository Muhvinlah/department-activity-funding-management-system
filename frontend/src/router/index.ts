// Updated router/index.ts (fixing your current version)
import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';

import { useAuthStore } from '@/stores/authStore';
import { USER_ROLES, ADMIN_ROLES } from '@/constants/userRoles';
import mainLayout from '@/layouts/mainLayout.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/login',
    component: () => import('../views/Login.vue'),
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/app',
    component: mainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'home',
        name: 'Home',
        component: () => import('../views/Home.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'tor',
        name: 'TOR',
        component: () => import('../views/submitTOR.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'lpj',
        name: 'LPJ',
        component: () => import('../views/submitLPJ.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { requiresAuth: true }
      }
    ]
  },
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/:catchAll(.*)',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = !!authStore.token;
  const userRole = authStore.user?.role;

  // If route requires auth and user is not authenticated, redirect to login
  if (to.meta?.requiresAuth && !isAuthenticated) {
    return next({ name: 'Login' });
  }

  // If user is authenticated and tries to access guest-only routes (like login)
  if (to.meta?.guestOnly && isAuthenticated) {
    // Redirect based on role
    if (ADMIN_ROLES.includes(userRole)) {
      return next({ name: 'Dashboard' });
    }
    return next({ name: 'Home' });
  }

  // Check role-based access
  if (to.meta?.roles && Array.isArray(to.meta.roles)) {
    if (!userRole || !to.meta.roles.includes(userRole)) {
      // User doesn't have required role
      if (ADMIN_ROLES.includes(userRole)) {
        return next({ name: 'Dashboard' });
      }
      return next({ name: 'Home' });
    }
  }

  next();
});

export default router;