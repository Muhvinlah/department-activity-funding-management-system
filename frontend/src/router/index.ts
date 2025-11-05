import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/counter';

import MainLayout from '../layouts/MainLayout.vue';

// Role Definitions
const standardUser = ['lecturer', 'student'];
const adminUser = ['headmaster', 'secretary', 'admin'];

// Route Definitions
const routes: Array<RouteRecordRaw> = [
  // Login
  {
    path: '/login',
    component: () => import('../views/Login.vue'),
    meta: { requiresAuth: false }
  },

  // Main Application
  {
    path: '/app',
    component: MainLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'home',
        name: 'Home',
        component: () => import('../views/Home.vue'),
        meta: { requiresAuth: false },
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue'),
        meta: { requiresAuth: false},
      },

      // Standard User Routes
      {
        path: 'tor/submit',
        name: 'SubmitTOR',
        component: () => import('../views/SubmitTOR.vue'),
        meta: { requiresAuth: false, roles: standardUser },
      },
      {
        path: 'lpj/submit/:torId',
        name: 'SubmitLPJ',
        component: () => import('../views/SubmitLPJ.vue'),
        meta: { requiresAuth: false, roles: standardUser },
      }
    ]
  },

  // Not Found Route
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

  // If the user is already authenticated and attempts to open the guest-only routes,
  // redirect them to the correct home based on role.
  if (to.meta?.guestOnly && isAuthenticated) {
    if (userRole && adminUser.includes(userRole)) {
      if (to.name !== 'admin-home') return next({ name: 'admin-home' });
      return next();
    }
    // redirect standard users to Home
    if (to.name !== 'Home') return next({ name: 'Home' });
    return next();
  }

  // Route requires authentication
  if (to.meta?.requiresAuth && !isAuthenticated) {
    // navigate to the named Login route that exists
    if (to.name !== 'Login') return next({ name: 'Login' });
    return next();
  }

  // Route requires a specific role
  if (to.meta?.roles && Array.isArray(to.meta.roles)) {
    if (!userRole) {
      // user has no role (not logged in) -> send to login
      if (to.name !== 'Login') return next({ name: 'Login' });
      return next();
    }
    if (!to.meta.roles.includes(userRole)) {
      // User does not have the required role, redirect them appropriately
      if (adminUser.includes(userRole)) {
        if (to.name !== 'admin-home') return next({ name: 'admin-home' });
        return next();
      }
      // default landing for standard users
      if (to.name !== 'Home') return next({ name: 'Home' });
      return next();
    }
  }

  // If all checks pass, proceed
  return next();
});

export default router;