export const USER_ROLES = {
  STUDENT: 'student',
  DEPARTMENT_ADMIN: 'department_admin', 
  SECRETARY: 'secretary',
  DEPARTMENT_HEAD: 'department_head'
} as const;

export const ADMIN_ROLES = [
  USER_ROLES.DEPARTMENT_ADMIN,
  USER_ROLES.SECRETARY, 
  USER_ROLES.DEPARTMENT_HEAD
];