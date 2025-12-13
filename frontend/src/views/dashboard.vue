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

        <!-- Admin-only Section (Conditional) -->
        <div v-if="showAdminSection" class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200 mb-8">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Administratif</h2>
          <div class="flex flex-wrap gap-4">
            <button 
              v-if="canManageBudget"
              @click="openBudgetModal"
              class="bg-[#0d7d90] text-white px-6 py-2 rounded-lg hover:bg-[#0a6a7a] transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Kelola Anggaran Tahunan
            </button>
            <button 
              @click="printDashboard"
              class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Export PDF
            </button>
            <button 
              @click="exportToCSV"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Export Excel
            </button>
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
        
        <!-- Approved Submissions Table -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200 mb-8">
          <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Pengajuan {{ isStudentOrLecturer ? 'Disetujui' : '' }}</h2>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
              <!-- Search -->
              <div class="relative">
                <input 
                  type="text" 
                  v-model="filters.search" 
                  placeholder="Cari Kegiatan / PIC..." 
                  class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] w-full"
                >
                <div class="absolute left-3 top-2.5 text-gray-400">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
              
              <!-- Filter Type -->
              <select 
                v-model="filters.type" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90]"
              >
                <option value="all">Semua Tipe</option>
                <option value="TOR">TOR</option>
                <option value="LPJ">LPJ</option>
              </select>

              <!-- Filter Category -->
              <select 
                v-model="filters.category" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90]"
              >
                <option value="all">Semua Kategori</option>
                <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
              </select>

              <!-- Filter Year -->
              <select 
                v-model.number="filters.year" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90]"
              >
                <option :value="0">Semua Tahun</option>
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
          </div>
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kegiatan</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggaran</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="paginatedSubmissions.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data ditemukan
                  </td>
                </tr>
                <tr v-for="item in paginatedSubmissions" :key="item.id_unique" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="item.type === 'TOR' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'"
                    >
                      {{ item.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.activity_name }}</div>
                    <div class="text-xs text-gray-500">{{ item.category }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.pic }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(item.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    Rp {{ formatCurrency(item.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getStatusClass(item.status)"
                    >
                      {{ formatStatus(item.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">
            <div class="flex flex-1 justify-between sm:hidden">
              <button 
                @click="pagination.currentPage--" 
                :disabled="pagination.currentPage === 1"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              <button 
                @click="pagination.currentPage++" 
                :disabled="pagination.currentPage >= totalPages"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <div class="flex items-center gap-4">
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ startIndex + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(endIndex, filteredSubmissions.length) }}</span>
                  of
                  <span class="font-medium">{{ filteredSubmissions.length }}</span>
                  results
                </p>
                <div class="flex items-center gap-2">
                   <span class="text-sm text-gray-700">Show</span>
                   <select 
                      v-model.number="pagination.itemsPerPage"
                      class="border-gray-300 rounded-md text-sm focus:ring-[#0d7d90] focus:border-[#0d7d90]"
                   >
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="25">25</option>
                      <option :value="50">50</option>
                   </select>
                   <span class="text-sm text-gray-700">entries</span>
                </div>
              </div>
              <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <button 
                    @click="pagination.currentPage--"
                    :disabled="pagination.currentPage === 1"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button 
                    v-for="page in visiblePages" 
                    :key="page"
                    @click="pagination.currentPage = page"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus:outline-offset-0"
                    :class="page === pagination.currentPage ? 'z-10 bg-[#0d7d90] text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0d7d90]' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'"
                  >
                    {{ page }}
                  </button>
                  <button 
                    @click="pagination.currentPage++"
                    :disabled="pagination.currentPage >= totalPages"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue' // watch added
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import dashboardService, { type ChartData } from '@/services/dashboardService'
import torService from '@/services/torService'
import lpjService from '@/services/lpjService'
import AnnualBudgetModal from '@/components/AnnualBudgetModal.vue'

// Add styles for printing
const printStyles = `
  @media print {
    /* Hide layout elements */
    nav, aside, .sidebar, header, .no-print {
      display: none !important;
    }
    
    /* Ensure content takes full width */
    body {
      background-color: white !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    
    /* Fix dashboard container for print */
    .min-h-screen {
      min-height: auto !important;
      padding: 0 !important;
      background-color: white !important;
    }
    
    .max-w-7xl {
      max-width: none !important;
      width: 100% !important;
    }
    
    /* Hide interactive elements in dashboard */
    button, input, select {
      display: none !important;
    }
    
    /* Ensure charts and tables break nicely */
    .grid {
      display: block !important;
    }
    
    .grid > div {
      page-break-inside: avoid;
      margin-bottom: 20px;
      border: 1px solid #ddd;
    }

    /* Force background colors */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    
    /* Show summary cards in grid for print if possible, or just stack */
    .grid-cols-1.md\\:grid-cols-3 {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 1rem;
    }

    /* Add header for print */
    .print-header {
      display: block !important;
      text-align: center;
      margin-bottom: 2rem;
    }
  }
  
  .print-header {
    display: none;
  }
`

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
const router = useRouter()
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

const isAdmin = computed(() => userRole.value === 'admin jurusan')
const isSecretary = computed(() => userRole.value === 'sekretaris jurusan')
const isHead = computed(() => userRole.value === 'ketua jurusan')
const isStudentOrLecturer = computed(() => ['mahasiswa', 'dosen'].includes(userRole.value || ''))

const canManageBudget = computed(() => isAdmin.value || isSecretary.value)
const showAdminSection = computed(() => isAdmin.value || isSecretary.value || isHead.value)

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

// Submissions Table Data
interface SubmissionItem {
  id: number;
  id_unique: string; // Combination of type and ID for key
  type: 'TOR' | 'LPJ';
  activity_name: string;
  pic: string;
  category: string;
  date: string;
  amount: number;
  status: string;
  raw_data: any;
}

const submissions = ref<SubmissionItem[]>([])
const categoryList = ref<string[]>([])
const filters = ref({
  search: '',
  type: 'all',
  year: 0,
  category: 'all'
})
const pagination = ref({
  currentPage: 1,
  itemsPerPage: 10
})



const availableYears = computed(() => {
  const years = new Set(submissions.value.map(item => new Date(item.date).getFullYear()))
  return Array.from(years).sort((a, b) => b - a)
})

const availableCategories = computed(() => {
  // If distinct database categories fetched, use them.
  // Otherwise/fallback could be computed from submissions if fetch failed, but requirement is "fetch from database".
  // We assume categoryList is populated with 'category_def' strings.
  return categoryList.value
})

const filteredSubmissions = computed(() => {
  return submissions.value.filter(item => {
    // 1. Search (Activity Name or PIC)
    const searchTerm = filters.value.search.toLowerCase()
    const matchesSearch = !searchTerm || 
                          item.activity_name.toLowerCase().includes(searchTerm) || 
                          item.pic.toLowerCase().includes(searchTerm)
    
    // 2. Filter Type
    const matchesType = filters.value.type === 'all' || item.type === filters.value.type
    
    // 3. Filter Year
    const itemYear = new Date(item.date).getFullYear()
    const matchesYear = filters.value.year === 0 || itemYear === filters.value.year

    // 4. Filter Category
    const matchesCategory = filters.value.category === 'all' || item.category === filters.value.category
    
    return matchesSearch && matchesType && matchesYear && matchesCategory
  })
})

const totalPages = computed(() => Math.ceil(filteredSubmissions.value.length / pagination.value.itemsPerPage))
const startIndex = computed(() => (pagination.value.currentPage - 1) * pagination.value.itemsPerPage)
const endIndex = computed(() => startIndex.value + pagination.value.itemsPerPage)

const paginatedSubmissions = computed(() => {
  return filteredSubmissions.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = pagination.value.currentPage
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }
  return pages.filter(p => typeof p === 'number') as number[] // Simplified for this view
})

// Reset pagination when filters change
watch(filters, () => {
  pagination.value.currentPage = 1
}, { deep: true })

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

const fetchSubmissions = async () => {
  try {
    const isRestricted = isStudentOrLecturer.value
    console.log('Fetching submissions, restricted mode:', isRestricted)
    
    let torData: any[] = []
    let lpjData: any[] = []
    
    // Fetch TORs
    const torResponse = isRestricted 
      ? await torService.getAllTors('approved_by_head')
      : await torService.getAllTors()
      
    if (torResponse.success && Array.isArray(torResponse.data)) {
        torData = torResponse.data
    } else if (torResponse.success && torResponse.data && Array.isArray(torResponse.data.data)) {
        // Handle paginated response structure if applicable
        torData = torResponse.data.data
    }
    
    // Fetch LPJs
    const lpjResponse = isRestricted
      ? await lpjService.getAllLpjs('approved_by_head')
      : await lpjService.getAllLpjs()
      
    if (lpjResponse.success && Array.isArray(lpjResponse.data)) {
        lpjData = lpjResponse.data
    } else if (lpjResponse.success && lpjResponse.data && Array.isArray(lpjResponse.data.data)) {
        lpjData = lpjResponse.data.data
    }
    
    // Normalize and merge
    const normalizedTors: SubmissionItem[] = torData.map((item: any) => ({
      id: item.tor_id,
      id_unique: `TOR-${item.tor_id}`,
      type: 'TOR',
      activity_name: item.activity_name,
      pic: item.pic || '-', // Ensure PIC exists
      category: item.category?.category_def || 'Uncategorized',
      date: item.created_at,
      amount: item.budget_submitted,
      status: item.status,
      raw_data: item
    }))
    
    const normalizedLpjs: SubmissionItem[] = lpjData.map((item: any) => ({
      id: item.lpj_id,
      id_unique: `LPJ-${item.lpj_id}`,
      type: 'LPJ',
      // Access nested TOR data safely
      activity_name: item.tor?.activity_name || 'Unknown Activity',
      pic: item.tor?.pic || item.user?.full_name || '-', 
      category: item.tor?.category?.category_def || 'Uncategorized',
      date: item.created_at,
      amount: item.budget_used,
      status: item.status,
      raw_data: item
    }))
    
    submissions.value = [...normalizedTors, ...normalizedLpjs].sort((a, b) => {
      // Sort by date descending
      return new Date(b.date).getTime() - new Date(a.date).getTime()
    })
    
    console.log(`Loaded ${submissions.value.length} submissions`)
    
  } catch (error) {
    console.error('Failed to fetch submissions:', error)
  }
}

const fetchCategories = async () => {
  try {
    const response = await dashboardService.getCategories();
    if (response.success && response.data) {
      // Assuming response.data is array of objects with { id, category_def, ... }
      categoryList.value = response.data.map((c: any) => c.category_def).sort();
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const formatStatus = (status: string) => {
  return ucwords(status.replace(/_/g, ' '))
}

const getStatusClass = (status: string) => {
  if (status.includes('approved') || status.includes('disetujui')) return 'bg-green-100 text-green-800'
  if (status.includes('rejected') || status.includes('ditolak')) return 'bg-red-100 text-red-800'
  if (status.includes('revision') || status.includes('revisi')) return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
}

const viewDetail = (item: SubmissionItem) => {
  if (item.type === 'TOR') {
    router.push(`/app/review-tor/${item.id}`)
  } else {
    router.push(`/app/review-lpj/${item.id}`)
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
    },
    tooltip: {
      callbacks: {
        label: function(context: any) {
          const value = context.raw || 0;
          const total = context.chart._metasets[context.datasetIndex].total;
          const percentage = Math.round((value / total) * 100) + '%';
          return `${percentage}`;
        }
      }
    }
  }
}

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    },
    tooltip: {
      callbacks: {
        label: function(context: any) {
          const value = context.raw || 0;
          const total = context.chart._metasets[context.datasetIndex].total;
          const percentage = Math.round((value / total) * 100) + '%';
          return `${percentage}`;
        }
      }
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
  
  // Fetch submissions table data
  fetchSubmissions()

  // Fetch categories for filter
  fetchCategories()
  
  // Inject print styles
  const style = document.createElement('style')
  style.id = 'dashboard-print-style'
  style.textContent = printStyles
  document.head.appendChild(style)
})

onUnmounted(() => {
  // Cleanup interval
  if (clockInterval) {
    clearInterval(clockInterval)
  }
  
  // Remove print styles
  const style = document.getElementById('dashboard-print-style')
  if (style) {
    style.remove()
  }
})

// Export Functions
const printDashboard = () => {
  window.print()
}

const exportToCSV = () => {
  // Combine all submissions, filter if needed based on the current table view or export ALL approved.
  // Requirement: "export them as pdf/excel... includes details of activities"
  // We'll export the 'submissions' array which contains the merged TOR/LPJ data.
  
  if (submissions.value.length === 0) {
    alert('Tidak ada data untuk diexport')
    return
  }

  const headers = [
    'Type',
    'Activity Name',
    'Category',
    'PIC',
    'Date',
    'Budget',
    'Status'
  ]
  
  const csvContent = [
    headers.join(','),
    ...submissions.value.map(item => {
      const row = [
        item.type,
        `"${item.activity_name.replace(/"/g, '""')}"`, // Escape quotes
        `"${item.category.replace(/"/g, '""')}"`,
        `"${item.pic.replace(/"/g, '""')}"`,
        formatDate(item.date),
        item.amount,
        item.status
      ]
      return row.join(',')
    })
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `laporan_kegiatan_${new Date().toISOString().slice(0,10)}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
</script>

<style scoped>
/* Smooth transitions for the progress bar */
.progress-bar {
  transition: width 0.5s ease-in-out;
}
</style>