<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header with Clock -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-[#0d7d90]">
                <h1 class="text-3xl font-bold">Dashboard Kegiatan</h1>
                <p class="mt-2">Statistik pengajuan TOR dan LPJ jurusan</p>
            </div>
            <div class="text-right text-[#0d7d90]">
                <div class="text-2xl font-bold">
                    {{ currentTime }}
                </div>
                <p class="text-sm">{{ currentDate }}</p>
                <p class="text-sm">{{ currentPeriod }}</p>
            </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0d7d90]"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline"> {{ error }}</span>
      </div>

      <!-- Content -->
      <div v-else>
        <!-- Budget Summary at the Top -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200 mb-8">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Anggaran Jurusan</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Budget -->
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <p class="text-sm font-medium text-blue-800">Total Anggaran</p>
              <p class="text-2xl font-bold text-blue-900 mt-2">Rp {{ formatCurrency(budget.total) }}</p>
              <p class="text-xs text-blue-600 mt-1">Tahun {{ new Date().getFullYear() }}</p>
            </div>
            
            <!-- Terpakai -->
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <p class="text-sm font-medium text-green-800">Anggaran Terpakai</p>
              <p class="text-2xl font-bold text-green-900 mt-2">Rp {{ formatCurrency(budget.used) }}</p>
              <p class="text-xs text-green-600 mt-1">{{ budget.total > 0 ? ((budget.used / budget.total) * 100).toFixed(1) : 0 }}% dari total</p>
            </div>
            
            <!-- Tersedia -->
            <div class="text-center p-4 bg-orange-50 rounded-lg">
              <p class="text-sm font-medium text-orange-800">Sisa Tersedia</p>
              <p class="text-2xl font-bold text-orange-900 mt-2">Rp {{ formatCurrency(budget.available) }}</p>
              <p class="text-xs text-orange-600 mt-1">{{ budget.total > 0 ? ((budget.available / budget.total) * 100).toFixed(1) : 0 }}% dari total</p>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mt-6">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progress Penyerapan Anggaran</span>
              <span>{{ budget.total > 0 ? ((budget.used / budget.total) * 100).toFixed(1) : 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div 
                class="bg-green-600 h-3 rounded-full transition-all duration-500"
                :style="{ width: `${budget.total > 0 ? (budget.used / budget.total) * 100 : 0}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Main Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <!-- Total Kegiatan -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Kegiatan</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalKegiatan }}</p>
              </div>
              <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Semua pengajuan TOR</p>
          </div>

          <!-- Menunggu Persetujuan -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Menunggu Persetujuan</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.menungguPersetujuan }}</p>
              </div>
              <div class="p-3 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Perlu tindakan</p>
          </div>

          <!-- Disetujui -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Disetujui</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.disetujui }}</p>
              </div>
              <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Kegiatan aktif</p>
          </div>

          <!-- Ditolak -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Ditolak</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.ditolak }}</p>
              </div>
              <div class="p-3 bg-red-100 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Perlu revisi</p>
          </div>

          <!-- LPJ Diajukan -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">LPJ Diajukan</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.lpjDiajukan }}</p>
              </div>
              <div class="p-3 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Laporan pertanggungjawaban</p>
          </div>

          <!-- LPJ Disetujui -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">LPJ Disetujui</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.lpjDisetujui }}</p>
              </div>
              <div class="p-3 bg-indigo-100 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Selesai diverifikasi</p>
          </div>
        </div>

        <!-- Charts and Visualizations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Status Distribution Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Status Kegiatan</h2>
            <div class="h-64 flex items-center justify-center">
              <!-- Placeholder for chart - in real app, use Chart.js or similar -->
              <div class="text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p>Grafik distribusi status akan ditampilkan di sini</p>
              </div>
            </div>
          </div>

          <!-- Monthly Trend -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Trend Pengajuan Bulanan</h2>
            <div class="h-64 flex items-center justify-center">
              <!-- Placeholder for chart -->
              <div class="text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
                <p>Grafik trend bulanan akan ditampilkan di sini</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Admin-only Section (Conditional) -->
        <div v-if="user.role === 'admin' || user.role === 'department_chair'" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Administratif</h2>
          <div class="flex gap-4">
            <button class="bg-[#0d7d90] text-white px-6 py-2 rounded-lg hover:bg-[#0a6a7a] transition-colors">
              Export Laporan
            </button>
            <button class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
              Kelola Pengguna
            </button>
            <button class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
              Settings
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import dashboardService from '@/services/dashboardService'

// User data
const authStore = useAuthStore()
const user = computed(() => authStore.user || { role: 'student' })

// Real-time clock
const currentTime = ref('')
const currentDate = ref('')

// Simple automatic period detection
const currentPeriod = computed(() => {
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth() + 1
  
  // Simple logic:
  // - February to July: Semester Genap (Even)
  // - August to January: Semester Ganjil (Odd)
  if (month >= 2 && month <= 7) {
    return `Semester Genap ${year-1}/${year}`
  } else {
    return `Semester Ganjil ${year}/${year+1}`
  }
})

// Statistics data
const loading = ref(true)
const error = ref('')

const stats = ref({
  totalKegiatan: 0,
  menungguPersetujuan: 0,
  disetujui: 0,
  ditolak: 0,
  lpjDiajukan: 0,
  lpjDisetujui: 0
})

const budget = ref({
  total: 0,
  used: 0,
  available: 0
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount || 0)
}

const updateClock = () => {
  const now = new Date()
  
  // Format time
  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
  
  // Format date
  currentDate.value = now.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const fetchDashboardStats = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await dashboardService.getSummary()
    
    if (response.success && response.data) {
      const data = response.data
      
      // Update Budget
      if (data.budget_info) {
        budget.value = {
          total: data.budget_info.total_budget,
          used: data.budget_info.used_budget,
          available: data.budget_info.remaining_budget
        }
      }
      
      // Update Stats
      // Calculate total pending TORs
      const pendingTor = (data.pending_approvals.tor_submitted || 0) + 
                         (data.pending_approvals.tor_reviewed || 0) + 
                         (data.pending_approvals.tor_verified || 0)
                         
      // Calculate total pending LPJs
      const pendingLpj = (data.pending_approvals.lpj_submitted || 0) + 
                         (data.pending_approvals.lpj_reviewed || 0) + 
                         (data.pending_approvals.lpj_verified || 0)
      
      stats.value = {
        totalKegiatan: data.overview.total_tor,
        menungguPersetujuan: pendingTor,
        disetujui: data.tor_statistics['approved_by_head'] || 0,
        ditolak: data.tor_statistics['rejected'] || 0,
        lpjDiajukan: pendingLpj,
        lpjDisetujui: data.lpj_statistics['approved_by_head'] || 0
      }
    } else {
      error.value = response.message || 'Failed to load dashboard data'
    }
  } catch (err) {
    error.value = 'An error occurred while fetching data'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Lifecycle
let clockInterval

onMounted(() => {
  // Initialize clock
  updateClock()
  
  // Update clock every second
  clockInterval = setInterval(updateClock, 1000)
  
  // Fetch statistics from API
  fetchDashboardStats()
})

onUnmounted(() => {
  // Cleanup interval
  if (clockInterval) {
    clearInterval(clockInterval)
  }
})
</script>

<style scoped>
/* Smooth transitions for the progress bar */
.progress-bar {
  transition: width 0.5s ease-in-out;
}
</style>