/// <reference types="vite/client" />
declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// Deklarasi untuk variabel lingkungan yang diekspos oleh Vite
interface ImportMetaEnv {
  readonly VITE_API_BASE_URL: string
  // Tambahkan variabel lingkungan kustom lainnya di sini
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}