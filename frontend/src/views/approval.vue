<template>
    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Approval Management</h1>
            <p class="text-gray-600">Review and approve TOR and LPJ submissions</p>
        </div>

        <!-- Tabs -->
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
                        ? 'border-blue-500 text-blue-600'
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

        <!-- Filter Bar -->
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <select 
                v-model="filters.status"
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="needs_revision">Needs Revision</option>
            </select>

            <select 
                v-model="filters.department"
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
                </option>
            </select>

            <input
                v-model="filters.search"
                type="text"
                placeholder="Search by activity name or submitter..."
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 flex-1 min-w-[300px]"
            >
        </div>

        <!-- Content -->
        <div v-if="isLoading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <div v-else>
            <!-- TOR Approval List -->
            <div v-if="activeTab === 'tor'" class="space-y-4">
                <div
                v-for="tor in filteredTORList"
                :key="tor.id"
                class="bg-white rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow"
                >
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ tor.activityName }}</h3>
                        <p class="text-sm text-gray-600">Submitted by: {{ tor.submitterName }} ({{ tor.submitterNim }})</p>
                        <p class="text-sm text-gray-500">Department: {{ tor.departmentName }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="getStatusBadgeClass(tor.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ formatStatus(tor.status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">{{ formatDate(tor.submittedAt) }}</p>
                    </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm">
                    <div>
                        <span class="font-medium">Budget:</span>
                        <p class="text-gray-700">Rp {{ formatCurrency(tor.budgetPlan) }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Schedule:</span>
                        <p class="text-gray-700">{{ formatDate(tor.scheduleStart) }} to {{ formatDate(tor.scheduleEnd) }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Current Stage:</span>
                        <p class="text-gray-700">{{ getCurrentStage(tor) }}</p>
                    </div>
                    </div>

                    <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button
                        @click="viewTORDetail(tor.id)"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                        View Details
                        </button>
                        <button
                        v-if="tor.supportingDocuments && tor.supportingDocuments.length > 0"
                        @click="downloadDocuments(tor.supportingDocuments)"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                        Download Documents
                        </button>
                    </div>
                    <div v-if="canApprove(tor)" class="flex space-x-2">
                        <button
                        @click="handleApproval(tor.id, 'approve')"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                        Approve
                        </button>
                        <button
                        @click="handleApproval(tor.id, 'reject')"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                        Reject
                        </button>
                        <button
                        @click="handleApproval(tor.id, 'revision')"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                        >
                        Request Revision
                        </button>
                    </div>
                    </div>
                </div>
                </div>

                <div v-if="filteredTORList.length === 0" class="text-center py-8 text-gray-500">
                No TOR submissions found matching your filters.
                </div>
            </div>

            <!-- LPJ Approval List -->
            <div v-if="activeTab === 'lpj'" class="space-y-4">
                <div
                v-for="lpj in filteredLPJList"
                :key="lpj.id"
                class="bg-white rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow"
                >
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ lpj.activityName }}</h3>
                        <p class="text-sm text-gray-600">Based on TOR: {{ lpj.torReference }}</p>
                        <p class="text-sm text-gray-500">Submitted by: {{ lpj.submitterName }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="getStatusBadgeClass(lpj.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ formatStatus(lpj.status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">{{ formatDate(lpj.submittedAt) }}</p>
                    </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 text-sm">
                    <div>
                        <span class="font-medium">Planned Budget:</span>
                        <p class="text-gray-700">Rp {{ formatCurrency(lpj.plannedBudget) }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Actual Budget:</span>
                        <p class="text-gray-700">Rp {{ formatCurrency(lpj.actualBudget) }}</p>
                    </div>
                    <div>
                        <span class="font-medium">Variance:</span>
                        <p :class="getVarianceClass(lpj.budgetVariance)" class="font-medium">
                        {{ lpj.budgetVariance > 0 ? '+' : '' }}{{ formatCurrency(lpj.budgetVariance) }}
                        </p>
                    </div>
                    <div>
                        <span class="font-medium">Current Stage:</span>
                        <p class="text-gray-700">{{ getCurrentStage(lpj) }}</p>
                    </div>
                    </div>

                    <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button
                        @click="viewLPJDetail(lpj.id)"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                        View Details
                        </button>
                        <button
                        v-if="lpj.supportingDocuments && lpj.supportingDocuments.length > 0"
                        @click="downloadDocuments(lpj.supportingDocuments)"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                        Download Documents
                        </button>
                    </div>
                    <div v-if="canApprove(lpj)" class="flex space-x-2">
                        <button
                        @click="handleLPJApproval(lpj.id, 'approve')"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                        Approve
                        </button>
                        <button
                        @click="handleLPJApproval(lpj.id, 'reject')"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                        Reject
                        </button>
                        <button
                        @click="handleLPJApproval(lpj.id, 'revision')"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                        >
                        Request Revision
                        </button>
                    </div>
                    </div>
                </div>
                </div>

                <div v-if="filteredLPJList.length === 0" class="text-center py-8 text-gray-500">
                No LPJ submissions found matching your filters.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

interface TOR {
  id: number;
  activityName: string;
  submitterName: string;
  submitterNim: string;
  departmentName: string;
  budgetPlan: number;
  scheduleStart: string;
  scheduleEnd: string;
  status: string;
  submittedAt: string;
  currentStage: string;
  supportingDocuments: Document[];
}

interface LPJ {
  id: number;
  activityName: string;
  torReference: string;
  submitterName: string;
  plannedBudget: number;
  actualBudget: number;
  budgetVariance: number;
  status: string;
  submittedAt: string;
  currentStage: string;
  supportingDocuments: Document[];
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
const activeTab = ref<'tor' | 'lpj'>('tor');
const isLoading = ref(false);
const torList = ref<TOR[]>([]);
const lpjList = ref<LPJ[]>([]);
const departments = ref([]);

const filters = ref({
  status: '',
  department: '',
  search: ''
});

// Tabs configuration
const tabs = [
  { id: 'tor', name: 'TOR Approvals' },
  { id: 'lpj', name: 'LPJ Approvals' }
];

// Computed properties
const filteredTORList = computed(() => {
    return torList.value.filter(tor => {
        const matchesStatus = !filters.value.status || tor.status === filters.value.status;
        const matchesDepartment = !filters.value.department || tor.departmentName.includes(filters.value.department);
        const matchesSearch = !filters.value.search || 
            tor.activityName.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            tor.submitterName.toLowerCase().includes(filters.value.search.toLowerCase());
    
        return matchesStatus && matchesDepartment && matchesSearch;
  });
});

const filteredLPJList = computed(() => {
    return lpjList.value.filter(lpj => {
        const matchesStatus = !filters.value.status || lpj.status === filters.value.status;
        const matchesSearch = !filters.value.search || 
            lpj.activityName.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            lpj.submitterName.toLowerCase().includes(filters.value.search.toLowerCase());
        
        return matchesStatus && matchesSearch;
  });
});

// Methods
const getTabCount = (tabId: string) => {
    if (tabId === 'tor') return filteredTORList.value.length;
    if (tabId === 'lpj') return filteredLPJList.value.length;
    return 0;
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    needs_revision: 'bg-orange-100 text-orange-800',
    draft: 'bg-gray-100 text-gray-800'
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status: string) => {
  const statusMap = {
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected',
    needs_revision: 'Needs Revision',
    draft: 'Draft'
  };
  return statusMap[status] || status;
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID').format(amount);
};

const getVarianceClass = (variance: number) => {
  if (variance > 0) return 'text-red-600';
  if (variance < 0) return 'text-green-600';
  return 'text-gray-600';
};

const getCurrentStage = (item: TOR | LPJ) => {
  // This would be determined based on the item's status and workflow
  return item.currentStage || 'Pending Review';
};

const canApprove = (item: TOR | LPJ) => {
  const userRole = authStore.user?.role;
  const itemStatus = item.status;
  
  // Logic to determine if current user can approve based on role and current status
  // This would be more complex based on your workflow
  return itemStatus === 'pending' || itemStatus === 'needs_revision';
};

// Navigation methods
const viewTORDetail = (torId: number) => {
  router.push({ name: 'TORApprovalDetail', params: { id: torId } });
};

const viewLPJDetail = (lpjId: number) => {
  router.push({ name: 'LPJApprovalDetail', params: { id: lpjId } });
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

const handleApproval = async (torId: number, action: string) => {
  // Implementation for TOR approval actions
  console.log(`TOR ${action}:`, torId);
  // API call would go here
};

const handleLPJApproval = async (lpjId: number, action: string) => {
  // Implementation for LPJ approval actions
  console.log(`LPJ ${action}:`, lpjId);
  // API call would go here
};

// Lifecycle
onMounted(async () => {
  isLoading.value = true;
  try {
    // Fetch initial data
    // await fetchTORList();
    // await fetchLPJList();
    // await fetchDepartments();
    
    // Mock data for demonstration
    torList.value = [
      {
        id: 1,
        activityName: 'Seminar Kewirausahaan 2024',
        submitterName: 'Ahmad Rizki',
        submitterNim: '202151234',
        departmentName: 'Teknik Informatika',
        budgetPlan: 15000000,
        scheduleStart: '2024-03-15',
        scheduleEnd: '2024-03-16',
        status: 'pending',
        submittedAt: '2024-02-01',
        currentStage: 'Secretary Review',
        supportingDocuments: [
          { id: 1, name: 'proposal.pdf', url: '/documents/proposal1.pdf', type: 'proposal' },
          { id: 2, name: 'rab.xlsx', url: '/documents/rab1.xlsx', type: 'budget' }
        ]
      }
    ];
    
    lpjList.value = [
      {
        id: 1,
        activityName: 'Seminar Kewirausahaan 2024',
        torReference: 'TOR/2024/001',
        submitterName: 'Ahmad Rizki',
        plannedBudget: 15000000,
        actualBudget: 14500000,
        budgetVariance: -500000,
        status: 'pending',
        submittedAt: '2024-03-20',
        currentStage: 'Admin Verification',
        supportingDocuments: [
          { id: 1, name: 'lpj_report.pdf', url: '/documents/lpj1.pdf', type: 'report' },
          { id: 2, name: 'receipts.zip', url: '/documents/receipts1.zip', type: 'receipts' }
        ]
      }
    ];
  } catch (error) {
    console.error('Error fetching approval data:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>