<template>
  <!-- Student View -->
  <div v-if="userRole === 'student'" class="student-home">
    <h2 class="text-2xl md:text-3xl font-bold text-[#0d7d90] p-6">Daftar Pengajuan Kegiatan</h2>
    
    <div class="flex flex-col px-12 space-y-8">
      <div 
        v-for="submission in studentSubmissions" 
        :key="submission.id"
        @click="viewStudentSubmission(submission)"
        class="border-b-2 space-y-6 cursor-pointer hover:bg-gray-50 p-4 rounded-lg transition-colors"
      >
        <div class="flex justify-between items-center">
          <span class="font-bold text-xl">{{ submission.activityName }}</span>
          <span :class="getStatusBadgeClass(submission.status)" class="p-2 rounded-xl text-sm font-medium">
            {{ formatStudentStatus(submission.status) }}
          </span>
        </div>
        <div class="flex text-md space-x-8 pt-2">
          <span>Anggaran: Rp {{ formatCurrency(submission.budget) }}</span>
          <span>Jadwal: {{ formatDate(submission.schedule) }}</span>
        </div>
        <div v-if="submission.revisionNotes" class="text-sm text-orange-600 bg-orange-50 p-2 rounded">
          <strong>Catatan Revisi:</strong> {{ submission.revisionNotes }}
        </div>
      </div>

      <div v-if="studentSubmissions.length === 0" class="text-center py-8 text-[#0d7d90]">
        Belum ada pengajuan kegiatan. 
        <router-link to="/app/tor" class="text-blue-600 hover:underline">Ajukan TOR baru</router-link>
      </div>
    </div>
  </div>

  <!-- Admin View -->
  <div v-else class="admin-home">
    <div class="container mx-auto p-6">
      <!-- Tabs for TOR vs LPJ -->
      <div class="mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.id
                  ? 'border-[#0d7d90] text-[#0d7d90]'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.name }}
              <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">
                {{ getTabCount(tab.id) }}
              </span>
            </button>
          </nav>
        </div>
      </div>

      <!-- Table Controls -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 p-4 rounded-lg">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <label for="entries-select" class="text-sm text-[#0d7d90]">Show:</label>
            <select 
              id="entries-select"
              v-model="perPage" 
              class="border border-[#0d7d90] rounded-md px-3 py-1 text-sm text-[#0d7d90] focus:outline-none focus:ring-2 focus:ring-[#0d7d90]/25"
            >
              <option 
                v-for="option in entriesOptions"
                :key="option"
                :value="option"
              >
                {{ option }}
              </option>
            </select>
            <span class="text-sm text-[#0d7d90]">entries</span>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari Pengajuan..."
              class="pl-10 pr-4 py-2 text-gray-600 border border-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent w-64"
            >
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
              <ion-icon name="search" class="text-[#0d7d90]"></ion-icon>
            </div>
          </div>

          <button 
            @click="toggleFilters"
            class="px-4 py-2 bg-[#0d7d90] rounded-xl text-[#f6f5f4] hover:bg-[#3d97a6] focus:outline-none focus:ring-2 focus:ring-[#0d7d90]/25 flex items-center gap-2"
          >
            <ion-icon name="filter"></ion-icon>
            Filter
          </button>

          <button 
            @click="exportData"
            class="px-4 py-2 bg-[#0d7d90] text-[#f6f5f4] rounded-xl hover:bg-[#3d97a6] focus:outline-none focus:ring-2 focus:ring-[#0d7d90]/25 flex items-center gap-2"
          >
            <ion-icon name="download-outline"></ion-icon>
            Export
          </button>
        </div>
      </div>

      <!-- Additional Filters (Conditional) -->
      <div v-if="showFilters" class="mb-6 p-4 bg-gray-50 rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="under_review">Under Review</option>
            <option value="needs_revision">Needs Revision</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select v-model="filters.department" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
          <select v-model="filters.year" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Years</option>
            <option v-for="year in yearOptions" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="clearFilters"
            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-[#0d7d90]">
            <thead>
              <tr>
                <th 
                  v-for="column in currentColumns"
                  :key="column.key"
                  @click="sortBy(column.key)"
                  class="px-6 py-3 text-left text-xs font-bold text-[#0d7d90] uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    {{ column.label }}
                    <span v-if="sortField === column.key" class="text-gray-400">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-[#0d7d90] uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#0d7d90]">
              <tr 
                v-for="(item, index) in paginatedData" 
                :key="item.id"
                class="hover:bg-gray-50 transition-colors cursor-pointer"
                @click="viewSubmission(item)"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ getItemNumber(index) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.activityName }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.submitterName }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.department }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(item.budget) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(item.submittedAt) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClasses(item.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center gap-2" @click.stop>
                    <button
                      @click="viewSubmission(item)"
                      class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50 transition-colors"
                      title="View Details"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      v-if="item.supportingDocuments && item.supportingDocuments.length > 0"
                      @click="downloadDocuments(item.supportingDocuments)"
                      class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50 transition-colors"
                      title="Download Documents"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedData.length === 0">
                <td :colspan="currentColumns.length + 1" class="px-6 py-8 text-center text-sm text-[#0d7d90]">
                  No {{ activeTab.toUpperCase() }} submissions found matching your criteria.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 flex items-center justify-between border-t border-[#0d7d90] sm:px-6">
          <div class="flex-1 flex justify-between items-center">
            <div class="text-sm text-[#0d7d90]">
              Showing <span class="font-medium">{{ showingStart }}</span> to <span class="font-medium">{{ showingEnd }}</span> of 
              <span class="font-medium">{{ totalEntries }}</span> results
            </div>
            <div class="flex gap-2">
              <button
                @click="previousPage"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-[#f6f5f4] bg-[#0d7d90] hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-[#f6f5f4] bg-[#0d7d90] hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

interface Submission {
  id: number;
  type: 'tor' | 'lpj';
  activityName: string;
  submitterName: string;
  department: string;
  budget: number;
  submittedAt: string;
  status: string;
  currentStage: string;
  supportingDocuments: Document[];
  revisionNotes?: string;
  schedule?: string;
}

interface StudentSubmission {
  id: number;
  activityName: string;
  budget: number;
  schedule: string;
  status: string;
  revisionNotes?: string;
  type: 'tor' | 'lpj';
}

interface Document {
  id: number;
  name: string;
  url: string;
  type: string;
}

const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const userRole = computed(() => authStore.user?.role);
const activeTab = ref<'tor' | 'lpj'>('tor');
const submissions = ref<Submission[]>([]);
const studentSubmissions = ref<StudentSubmission[]>([]);
const isLoading = ref(false);
const perPage = ref(10);
const currentPage = ref(1);
const searchQuery = ref('');
const showFilters = ref(false);
const sortField = ref('submittedAt');
const sortDirection = ref('desc');

const filters = ref({
  status: '',
  department: '',
  year: ''
});

// Constants
const tabs = [
  { id: 'tor', name: 'TOR Submissions' },
  { id: 'lpj', name: 'LPJ Submissions' }
];

const torColumns = [
  { key: 'no', label: 'No' },
  { key: 'activityName', label: 'Activity Name' },
  { key: 'submitterName', label: 'Submitted By' },
  { key: 'department', label: 'Department' },
  { key: 'budget', label: 'Budget' },
  { key: 'submittedAt', label: 'Submitted Date' },
  { key: 'status', label: 'Status' }
];

const lpjColumns = [
  { key: 'no', label: 'No' },
  { key: 'activityName', label: 'Activity Name' },
  { key: 'submitterName', label: 'Submitted By' },
  { key: 'department', label: 'Department' },
  { key: 'budget', label: 'Actual Budget' },
  { key: 'submittedAt', label: 'Submitted Date' },
  { key: 'status', label: 'Status' }
];

const entriesOptions = [10, 25, 50, 100];
const yearOptions = [2023, 2024, 2025];
const departments = [
  { id: 'ti', name: 'Teknik Informatika' },
  { id: 'si', name: 'Sistem Informasi' },
  { id: 'mi', name: 'Manajemen Informatika' }
];

// Computed properties
const userRoleDisplay = computed(() => {
  const roleMap = {
    'department_admin': 'Department Administrator',
    'secretary': 'Secretary',
    'department_head': 'Department Head',
    'student': 'Student'
  };
  return roleMap[userRole.value] || userRole.value;
});

const currentColumns = computed(() => {
  return activeTab.value === 'tor' ? torColumns : lpjColumns;
});

const filteredData = computed(() => {
  let filtered = submissions.value.filter(sub => sub.type === activeTab.value);
  
  // Filter by role-appropriate status
  filtered = filtered.filter(sub => isRelevantForCurrentRole(sub));
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      item.activityName.toLowerCase().includes(query) ||
      item.submitterName.toLowerCase().includes(query) ||
      item.department.toLowerCase().includes(query)
    );
  }
  
  // Apply other filters
  if (filters.value.status) {
    filtered = filtered.filter(item => item.status === filters.value.status);
  }
  
  if (filters.value.department) {
    filtered = filtered.filter(item => item.department === filters.value.department);
  }
  
  if (filters.value.year) {
    filtered = filtered.filter(item => new Date(item.submittedAt).getFullYear() === parseInt(filters.value.year));
  }
  
  return filtered;
});

const totalEntries = computed(() => filteredData.value.length);
const totalPages = computed(() => Math.ceil(totalEntries.value / perPage.value));
const showingStart = computed(() => (currentPage.value - 1) * perPage.value + 1);
const showingEnd = computed(() => {
  const end = currentPage.value * perPage.value;
  return end > totalEntries.value ? totalEntries.value : end;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  
  // Apply sorting
  const sorted = [...filteredData.value].sort((a, b) => {
    let aValue = a[sortField.value];
    let bValue = b[sortField.value];
    
    if (sortField.value === 'budget') {
      aValue = Number(aValue);
      bValue = Number(bValue);
    }
    
    if (sortField.value === 'submittedAt') {
      aValue = new Date(aValue).getTime();
      bValue = new Date(bValue).getTime();
    }
    
    if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  
  return sorted.slice(start, end);
});

// Methods
const getTabCount = (tabId: string) => {
  return submissions.value.filter(sub => 
    sub.type === tabId && isRelevantForCurrentRole(sub)
  ).length;
};

const isRelevantForCurrentRole = (submission: Submission) => {
  const status = submission.status;
  
  // Define which statuses are relevant for each role
  const roleStatusMap = {
    'secretary': ['submitted', 'under_review', 'needs_revision'],
    'department_admin': ['reviewed_by_secretary', 'verified_by_admin', 'needs_revision'],
    'department_head': ['verified_by_admin', 'approved_by_head', 'needs_revision']
  };
  
  return roleStatusMap[userRole.value]?.includes(status) || false;
};

const getItemNumber = (index: number) => (currentPage.value - 1) * perPage.value + index + 1;

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0
  }).format(amount);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatStatus = (status: string) => {
  const statusMap = {
    'submitted': 'Submitted',
    'under_review': 'Under Review',
    'reviewed_by_secretary': 'Reviewed by Secretary',
    'verified_by_admin': 'Verified by Admin',
    'approved_by_head': 'Approved by Head',
    'needs_revision': 'Needs Revision',
    'rejected': 'Rejected'
  };
  return statusMap[status] || status;
};

const formatStudentStatus = (status: string) => {
  const statusMap = {
    'submitted': 'Diajukan',
    'under_review': 'Diperiksa Sekretaris Jurusan',
    'reviewed_by_secretary': 'Diperiksa Sekretaris Jurusan',
    'verified_by_admin': 'Diverifikasi Admin',
    'approved_by_head': 'Disetujui',
    'needs_revision': 'Perlu Revisi',
    'rejected': 'Ditolak'
  };
  return statusMap[status] || status;
};

const getStatusClasses = (status: string) => {
  const statusClasses = {
    'submitted': 'bg-yellow-100 text-yellow-800',
    'under_review': 'bg-blue-100 text-blue-800',
    'reviewed_by_secretary': 'bg-purple-100 text-purple-800',
    'verified_by_admin': 'bg-indigo-100 text-indigo-800',
    'approved_by_head': 'bg-green-100 text-green-800',
    'needs_revision': 'bg-orange-100 text-orange-800',
    'rejected': 'bg-red-100 text-red-800'
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses = {
    'submitted': 'bg-[#FACC15]/25 text-[#FACC15]',
    'under_review': 'bg-[#FACC15]/25 text-[#FACC15]',
    'reviewed_by_secretary': 'bg-[#FACC15]/25 text-[#FACC15]',
    'verified_by_admin': 'bg-[#FACC15]/25 text-[#FACC15]',
    'approved_by_head': 'bg-[#0BC86F]/25 text-[#0BC86F]',
    'needs_revision': 'bg-orange-100 text-orange-800',
    'rejected': 'bg-[#D80300]/25 text-[#D80300]'
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const sortBy = (field: string) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};

const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

const clearFilters = () => {
  filters.value = { status: '', department: '', year: '' };
  searchQuery.value = '';
};

const previousPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const viewSubmission = (submission: Submission) => {
  if (submission.type === 'tor') {
    router.push(`/app/approval/tor/${submission.id}`);
  } else {
    router.push(`/app/approval/lpj/${submission.id}`);
  }
};

const viewStudentSubmission = (submission: StudentSubmission) => {
  if (submission.type === 'tor') {
    router.push(`/app/tor/${submission.id}`);
  } else {
    router.push(`/app/lpj/${submission.id}`);
  }
};

const downloadDocuments = (documents: Document[]) => {
  documents.forEach(doc => {
    const link = document.createElement('a');
    link.href = doc.url;
    link.download = doc.name;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  });
};

const exportData = () => {
  const dataToExport = filteredData.value;
  const csv = convertToCSV(dataToExport);
  downloadCSV(csv, `${activeTab.value}_submissions.csv`);
};

const convertToCSV = (data: any[]) => {
  if (data.length === 0) return '';
  
  const headers = ['Activity Name', 'Submitted By', 'Department', 'Budget', 'Submitted Date', 'Status'];
  const csvHeaders = headers.join(',');
  
  const csvRows = data.map(item => 
    [
      `"${item.activityName}"`,
      `"${item.submitterName}"`,
      `"${item.department}"`,
      item.budget,
      `"${formatDate(item.submittedAt)}"`,
      `"${formatStatus(item.status)}"`
    ].join(',')
  );

  return [csvHeaders, ...csvRows].join('\n');
};

const downloadCSV = (csv: string, filename: string) => {
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const fetchSubmissions = async () => {
  isLoading.value = true;
  try {
    if (userRole.value === 'student') {
      // Fetch student's own submissions
      studentSubmissions.value = [
        {
          id: 1,
          activityName: 'Seminar Kewirausahaan 2024',
          budget: 15000000,
          schedule: '15-16 Maret 2024',
          status: 'under_review',
          type: 'tor'
        },
        {
          id: 2,
          activityName: 'Workshop Web Development',
          budget: 8000000,
          schedule: '20 Februari 2024',
          status: 'approved_by_head',
          type: 'tor'
        },
        {
          id: 3,
          activityName: 'Pelatihan Public Speaking',
          budget: 5000000,
          schedule: '10 April 2024',
          status: 'needs_revision',
          revisionNotes: 'Mohon lengkapi dokumen RAB dan tambahkan detail peserta',
          type: 'tor'
        }
      ];
    } else {
      // Fetch admin submissions
      submissions.value = [
        {
          id: 1,
          type: 'tor',
          activityName: 'Seminar Kewirausahaan 2024',
          submitterName: 'Ahmad Rizki',
          department: 'Teknik Informatika',
          budget: 15000000,
          submittedAt: '2024-02-01',
          status: 'under_review',
          currentStage: 'Secretary Review',
          supportingDocuments: [
            { id: 1, name: 'proposal.pdf', url: '/documents/proposal1.pdf', type: 'proposal' }
          ]
        },
        {
          id: 2,
          type: 'lpj',
          activityName: 'Workshop Web Development',
          submitterName: 'Siti Nurhaliza',
          department: 'Sistem Informasi',
          budget: 8000000,
          submittedAt: '2024-02-15',
          status: 'verified_by_admin',
          currentStage: 'Department Head Approval',
          supportingDocuments: [
            { id: 1, name: 'lpj_report.pdf', url: '/documents/lpj1.pdf', type: 'report' }
          ]
        }
      ];
    }
  } catch (error) {
    console.error('Error fetching submissions:', error);
  } finally {
    isLoading.value = false;
  }
};

// Lifecycle
onMounted(() => {
  fetchSubmissions();
});
</script>