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
          <!-- Monthly Submissions Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Trend Pengajuan TOR & LPJ Bulanan</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Line :data="monthlySubmissionsChartData" :options="lineChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>

          <!-- Budget vs Realization Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Anggaran vs Realisasi (Top 10)</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Bar :data="budgetVsRealizationChartData" :options="barChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>

          <!-- TOR Status Distribution Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Status TOR</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Doughnut :data="torStatusChartData" :options="doughnutChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>

          <!-- LPJ Status Distribution Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Status LPJ</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Doughnut :data="lpjStatusChartData" :options="doughnutChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>

          <!-- TOR Budget by Category Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Anggaran TOR per Kategori</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Pie :data="torBudgetByCategoryChartData" :options="pieChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>

          <!-- LPJ Budget by Category Chart -->
          <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Realisasi LPJ per Kategori</h2>
            <div v-if="chartsLoading" class="h-64 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
            </div>
            <div v-else-if="chartData" class="h-64">
              <Pie :data="lpjBudgetByCategoryChartData" :options="pieChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              <p>No data available</p>
            </div>
          </div>
        </div>

        <!-- Admin-only Section (Conditional) -->
        <div v-if="userRole === 'admin jurusan' || userRole === 'ketua jurusan'" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Administratif</h2>
          <div class="flex flex-wrap gap-4">
            <button 
              @click="openBudgetModal"
              class="bg-[#0d7d90] text-white px-6 py-2 rounded-lg hover:bg-[#0a6a7a] transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Kelola Anggaran Tahunan
            </button>
            <button class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
              Export Laporan
            </button>
            <button class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors">
              Settings
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Annual Budget Modal -->
    <AnnualBudgetModal 
      :isOpen="showBudgetModal" 
      @close="closeBudgetModal"
      @updated="handleBudgetUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import dashboardService, { type ChartData } from '@/services/dashboardService'
import AnnualBudgetModal from '@/components/AnnualBudgetModal.vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line, Bar, Doughnut, Pie } from 'vue-chartjs'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

// User data
const authStore = useAuthStore()
const user = computed(() => authStore.user || {})
const userRole = computed(() => authStore.role || (authStore.user?.role?.role_def) || null)

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

// Charts data
const chartsLoading = ref(false)
const chartData = ref<ChartData | null>(null)

// Annual Budget Modal
const showBudgetModal = ref(false)

// Methods
const formatCurrency = (amount: number) => {
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

const fetchChartData = async () => {
  chartsLoading.value = true
  
  try {
    const response = await dashboardService.getChartData()
    
    if (response.success && response.data) {
      chartData.value = response.data
    }
  } catch (err) {
    console.error('Failed to fetch chart data:', err)
  } finally {
    chartsLoading.value = false
  }
}

// Chart configurations
const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        stepSize: 1
      }
    }
  }
}

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
}

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
}

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y' as const,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  },
  scales: {
    x: {
      beginAtZero: true
    }
  }
}

// Chart data computed properties
const monthlySubmissionsChartData = computed(() => {
  if (!chartData.value) return { labels: [], datasets: [] }
  
  return {
    labels: chartData.value.monthly_submissions.map(item => item.month),
    datasets: [
      {
        label: 'Pengajuan TOR',
        data: chartData.value.monthly_submissions.map(item => item.tor_count),
        borderColor: '#0d7d90',
        backgroundColor: 'rgba(13, 125, 144, 0.5)',
        tension: 0.4,
        fill: false
      },
      {
        label: 'Pengajuan LPJ',
        data: chartData.value.monthly_submissions.map(item => item.lpj_count),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.5)',
        tension: 0.4,
        fill: false
      }
    ]
  }
})

const torStatusChartData = computed(() => {
  if (!chartData.value) return { labels: [], datasets: [] }
  
  const torData = chartData.value.status_distribution.filter(item => item.type === 'TOR')
  
  const colors = [
    '#0d7d90', // teal
    '#10b981', // green
    '#f59e0b', // amber
    '#ef4444', // red
    '#8b5cf6', // purple
    '#3b82f6', // blue
  ]
  
  return {
    labels: torData.map(item => ucwords(item.status.replace(/_/g, ' '))),
    datasets: [
      {
        data: torData.map(item => item.count),
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff'
      }
    ]
  }
})

const lpjStatusChartData = computed(() => {
  if (!chartData.value) return { labels: [], datasets: [] }
  
  const lpjData = chartData.value.status_distribution.filter(item => item.type === 'LPJ')
  
  const colors = [
    '#0d7d90', // teal
    '#10b981', // green
    '#f59e0b', // amber
    '#ef4444', // red
    '#8b5cf6', // purple
    '#3b82f6', // blue
  ]
  
  return {
    labels: lpjData.map(item => ucwords(item.status.replace(/_/g, ' '))),
    datasets: [
      {
        data: lpjData.map(item => item.count),
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff'
      }
    ]
  }
})

const torBudgetByCategoryChartData = computed(() => {
  if (!chartData.value) return { labels: [], datasets: [] }
  
  const colors = [
    '#0d7d90',
    '#10b981',
    '#f59e0b',
    '#ef4444',
    '#8b5cf6',
    '#3b82f6',
    '#ec4899',
    '#14b8a6'
  ]
  
  return {
    labels: chartData.value.budget_by_category.map(item => item.category),
    datasets: [
      {
        data: chartData.value.budget_by_category.map(item => item.amount),
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff'
      }
    ]
  }
})

const lpjBudgetByCategoryChartData = computed(() => {
  if (!chartData.value || !chartData.value.lpj_budget_by_category) return { labels: [], datasets: [] }
  
  const colors = [
    '#0d7d90',
    '#10b981',
    '#f59e0b',
    '#ef4444',
    '#8b5cf6',
    '#3b82f6',
    '#ec4899',
    '#14b8a6'
  ]
  
  return {
    labels: chartData.value.lpj_budget_by_category.map(item => item.category),
    datasets: [
      {
        data: chartData.value.lpj_budget_by_category.map(item => item.amount),
        backgroundColor: colors,
        borderWidth: 2,
        borderColor: '#ffffff'
      }
    ]
  }
})

// Helper function to capitalize words
const ucwords = (str: string) => {
  return str.replace(/\b\w/g, l => l.toUpperCase())
}

const budgetVsRealizationChartData = computed(() => {
  if (!chartData.value) return { labels: [], datasets: [] }
  
  return {
    labels: chartData.value.budget_vs_realization.map(item => item.activity),
    datasets: [
      {
        label: 'Anggaran Diajukan',
        data: chartData.value.budget_vs_realization.map(item => item.budget_submitted),
        backgroundColor: '#0d7d90'
      },
      {
        label: 'Realisasi',
        data: chartData.value.budget_vs_realization.map(item => item.budget_used),
        backgroundColor: '#10b981'
      }
    ]
  }
})

// Annual Budget Modal handlers
const openBudgetModal = () => {
  showBudgetModal.value = true
}

const closeBudgetModal = () => {
  showBudgetModal.value = false
}

const handleBudgetUpdated = () => {
  // Refresh dashboard data when budget is updated
  fetchDashboardStats()
  fetchChartData()
}

// Lifecycle
let clockInterval: number

onMounted(() => {
  // Initialize clock
  updateClock()
  
  // Update clock every second
  clockInterval = setInterval(updateClock, 1000) as unknown as number
  
  // Fetch statistics from API
  fetchDashboardStats()
  
  // Fetch chart data
  fetchChartData()
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