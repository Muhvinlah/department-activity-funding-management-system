// Updated router/index.ts (fixing your current version)
import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';

import { useAuthStore } from '@/stores/authStore';
import { USER_ROLES, ADMIN_ROLES } from '@/constants/userRoles';
import mainLayout from '@/layouts/mainLayout.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/login.vue'),
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/app',
    component: mainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'account',
        name: 'AccountSettings',
        component: () => import('../views/accountProfile.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'home',
        name: 'Home',
        component: () => import('../views/home.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'tor/:id?',
        name: 'TOR',
        component: () => import('../views/submitTOR.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'lpj/:id?',
        name: 'LPJ',
        component: () => import('../views/submitLPJ.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/dashboard.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'approval/tor/:id',
        name: 'ReviewTOR',
        component: () => import('../views/reviewTOR.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'approval/lpj/:id',
        name: 'ReviewLPJ',
        component: () => import('../views/reviewLPJ.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'users',
        name: 'UserManagement',
        component: () => import('../views/userManagement.vue'),
        meta: { requiresAuth: true, roles: ['admin jurusan'] }
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
    component: () => import('../views/notFound.vue'),
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = !!authStore.token;
  const userRole = authStore.role as string | null;

  console.log(`Router: Navigating to ${to.path}, Auth: ${isAuthenticated}, Role: ${userRole}`);

  // If route requires auth and user is not authenticated, redirect to login
  if (to.meta?.requiresAuth && !isAuthenticated) {
    return next({ name: 'Login' });
  }

  // If user is authenticated and tries to access guest-only routes (like login)
  if (to.meta?.guestOnly && isAuthenticated) {
    // Redirect based on role
    if (userRole && ADMIN_ROLES.includes(userRole as any)) {
      return next({ name: 'Dashboard' });
    }
    return next({ name: 'Home' });
  }

  // Check role-based access
  if (to.meta?.roles && Array.isArray(to.meta.roles)) {
    if (!userRole || !to.meta.roles.includes(userRole as any)) {
      // User doesn't have required role
      console.warn(`Access denied to ${to.path}. User role: ${userRole}, Required roles: ${to.meta.roles}`);
      if (userRole && ADMIN_ROLES.includes(userRole as any)) {
        return next({ name: 'Dashboard' });
      }
      return next({ name: 'Home' });
    }
  }

  console.log(`Router: Access granted to ${to.path}`);
  next();
});

export default router;