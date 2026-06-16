export const USER_ROLES = {
  STUDENT: 'mahasiswa',
  LECTURER: 'dosen',
  SECRETARY: 'sekretaris jurusan', 
  ADMIN: 'admin jurusan',
  HEAD: 'ketua jurusan'
} as const;

export const ADMIN_ROLES = [
  USER_ROLES.SECRETARY,
  USER_ROLES.ADMIN,
  USER_ROLES.HEAD
];