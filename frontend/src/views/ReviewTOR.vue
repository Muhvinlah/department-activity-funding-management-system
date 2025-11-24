<template>
  <div class="min-h-screen bg-[#F6F5F4] py-12 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-[#0D7D90] rounded-2xl shadow-md p-8 mb-8">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h1 class="text-3xl font-bold text-[#f6f5f4] mb-2">Review TOR</h1>
            <p class="text-[#f6f5f4]">Review submitted Term of Reference</p>
          </div>
          <span :class="getStatusBadgeClass(tor?.status)" class="px-4 py-2 rounded-xl text-sm font-medium">
            {{ formatStatus(tor?.status) }}
          </span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-8 text-[#0d7d90]">
        Loading TOR details...
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-[#D80300] border border-[#D80300] rounded-lg p-4 mb-4">
        <p class="text-[#F6F5F4]">✗ {{ errorMessage }}</p>
      </div>

      <!-- Main Content -->
      <div v-else-if="tor" class="space-y-6">
        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow p-8 space-y-6">
          <!-- Activity Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Nama Kegiatan
            </label>
            <input
              type="text"
              :value="tor.activity_name"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            />
          </div>

          <!-- Activity Background -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Latar Belakang Kegiatan
            </label>
            <textarea
              :value="tor.activity_background"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed h-24"
            ></textarea>
          </div>

          <!-- Activity Purpose -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Tujuan Kegiatan
            </label>
            <textarea
              :value="tor.activity_purpose"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed h-24"
            ></textarea>
          </div>

          <!-- Participant -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Peserta
            </label>
            <textarea
              :value="tor.participant"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed h-20"
            ></textarea>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Mulai
              </label>
              <input
                type="date"
                :value="tor.start_date"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Berakhir
              </label>
              <input
                type="date"
                :value="tor.end_date"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
            </div>
          </div>

          <!-- Budget -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Anggaran Diajukan
            </label>
            <input
              type="text"
              :value="`Rp ${formatCurrency(tor.budget_submitted)}`"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            />
          </div>

          <!-- PIC -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Penanggung Jawab (PIC)
            </label>
            <input
              type="text"
              :value="tor.pic"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            />
          </div>
        </div>

        <!-- Supporting Documents -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Dokumen Pendukung</h2>
          <div v-if="tor.attachments && tor.attachments.length > 0" class="space-y-4">
            <div v-for="(file, index) in tor.attachments" :key="index" class="border border-gray-300 rounded-lg p-4">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-medium text-gray-800">{{ file.file_name }}</p>
                  <p class="text-sm text-gray-500">{{ formatFileSize(file.file_size) }}</p>
                </div>
                <button
                  @click="downloadFile(file)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                >
                  Download
                </button>
              </div>

              <!-- PDF Viewer -->
              <div v-if="isPDF(file.file_name)" class="mt-4 bg-gray-100 rounded h-96">
                <iframe
                  :src="file.file_path"
                  class="w-full h-full rounded"
                  type="application/pdf"
                ></iframe>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500 text-center py-8">
            Tidak ada dokumen yang diunggah
          </div>
        </div>

        <!-- Comments Section -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Komentar & Catatan Reviewer</h2>

          <!-- Comments List -->
          <div v-if="comments.length > 0" class="space-y-4 mb-6">
            <div v-for="(comment, index) in comments" :key="index" class="border-l-4 border-[#0d7d90] pl-4 py-2">
              <div class="flex justify-between items-start mb-1">
                <p class="font-medium text-gray-800">{{ comment.reviewer_name }}</p>
                <p class="text-sm text-gray-500">{{ formatDate(comment.created_at) }}</p>
              </div>
              <p class="text-sm text-gray-600 mb-1">
                <span class="font-medium">{{ comment.status }}:</span> {{ comment.message }}
              </p>
            </div>
          </div>

          <!-- Add Comment -->
          <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Tambah Komentar & Keputusan</p>
            <textarea
              v-model="newComment"
              placeholder="Tuliskan komentar atau catatan untuk submitter..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] h-24"
            ></textarea>
            <p class="text-xs text-gray-500 mt-2">
              Komentar wajib diisi. Klik "Setujui" atau "Tolak" untuk memproses keputusan Anda.
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-end">
          <div v-if="tor?.status === 'approved_by_head'" class="w-full bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-blue-700 font-medium">✓ Sudah disetujui oleh Ketua Jurusan. Tidak dapat diubah.</p>
          </div>
          <div v-else class="flex gap-4 w-full justify-end">
            <button
              @click="rejectSubmission"
              class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Tolak
            </button>
            <button
              @click="approveSubmission"
              class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
              Setujui
            </button>
          </div>
          <button
            @click="goBack"
            class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500"
          >
            Kembali
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import torService from '@/services/torService';
import { USER_ROLES } from '@/constants/userRoles';

interface TOR {
  tor_id: number;
  activity_name: string;
  activity_background: string;
  activity_purpose: string;
  participant: string;
  start_date: string;
  end_date: string;
  budget_submitted: number;
  pic: string;
  status: string;
  attachments: Array<{
    file_name: string;
    file_path: string;
    file_size: number;
  }>;
}

interface Comment {
  reviewer_name: string;
  created_at: string;
  status: string;
  message: string;
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const tor = ref<TOR | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');
const comments = ref<Comment[]>([]);
const newComment = ref('');
const commentStatus = ref('');
const isSubmitting = ref(false);

// Determine initial comment status based on current role
const getInitialCommentStatus = () => {
  const role = authStore.role;
  switch (role) {
    case USER_ROLES.SECRETARY:
      return 'reviewed_by_secretary';
    case USER_ROLES.ADMIN:
      return 'verified_by_admin';
    case USER_ROLES.HEAD:
      return 'approved_by_head';
    default:
      return 'needs_revision';
  }
};

const getApprovedStatus = () => {
  const role = authStore.role;
  switch (role) {
    case USER_ROLES.SECRETARY:
      return 'reviewed_by_secretary';
    case USER_ROLES.ADMIN:
      return 'verified_by_admin';
    case USER_ROLES.HEAD:
      return 'approved_by_head';
    default:
      return 'under_review';
  }
};

const getRejectionStatus = () => {
  const role = authStore.role;
  const roleName = getRoleDisplayName();
  switch (role) {
    case USER_ROLES.SECRETARY:
      return `needs_revision_by_${USER_ROLES.SECRETARY.replace(/\s+/g, '_')}`;
    case USER_ROLES.ADMIN:
      return `needs_revision_by_${USER_ROLES.ADMIN.replace(/\s+/g, '_')}`;
    case USER_ROLES.HEAD:
      return `needs_revision_by_${USER_ROLES.HEAD.replace(/\s+/g, '_')}`;
    default:
      return 'needs_revision';
  }
};

const getRoleDisplayName = () => {
  const role = authStore.role;
  switch (role) {
    case USER_ROLES.SECRETARY:
      return 'Sekretaris Jurusan';
    case USER_ROLES.ADMIN:
      return 'Admin Jurusan';
    case USER_ROLES.HEAD:
      return 'Ketua Jurusan';
    default:
      return 'Unknown';
  }
};

// Initialize comment status on mount
onMounted(() => {
  commentStatus.value = getInitialCommentStatus();
});

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

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const isPDF = (fileName: string) => {
  return fileName.toLowerCase().endsWith('.pdf');
};

const getStatusBadgeClass = (status?: string) => {
  const statusClasses: Record<string, string> = {
    'submitted': 'bg-yellow-100 text-yellow-800',
    'under_review': 'bg-blue-100 text-blue-800',
    'reviewed_by_secretary': 'bg-purple-100 text-purple-800',
    'verified_by_admin': 'bg-indigo-100 text-indigo-800',
    'approved_by_head': 'bg-green-100 text-green-800',
    'needs_revision': 'bg-orange-100 text-orange-800',
    'rejected': 'bg-red-100 text-red-800'
  };
  return statusClasses[status || ''] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status?: string) => {
  if (!status) return 'Unknown';
  
  // Handle role-specific revision statuses
  if (status.includes('needs_revision_by_')) {
    if (status.includes('sekretaris_jurusan')) {
      return 'Needs Revision by Sekretaris Jurusan';
    } else if (status.includes('admin_jurusan')) {
      return 'Needs Revision by Admin Jurusan';
    } else if (status.includes('ketua_jurusan')) {
      return 'Needs Revision by Ketua Jurusan';
    }
  }

  const statusMap: Record<string, string> = {
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

const downloadFile = (file: any) => {
  const link = document.createElement('a');
  link.href = file.file_path;
  link.download = file.file_name;
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const submitComment = async () => {
  if (!newComment.value.trim()) return;

  // TODO: Send comment to backend API
  // For now, add to local comments array
  comments.value.push({
    reviewer_name: getRoleDisplayName(),
    created_at: new Date().toISOString(),
    status: commentStatus.value,
    message: newComment.value
  });

  newComment.value = '';
  console.log('Comment submitted:', {
    tor_id: tor.value?.tor_id,
    status: commentStatus.value,
    message: newComment.value
  });
};

const approveSubmission = async () => {
  if (!tor.value) return;
  if (!newComment.value.trim()) {
    alert('Please add a comment before approving');
    return;
  }

  isSubmitting.value = true;
  try {
    const approvedStatus = getApprovedStatus();
    
    // Add approval comment
    comments.value.push({
      reviewer_name: getRoleDisplayName(),
      created_at: new Date().toISOString(),
      status: approvedStatus,
      message: `Approved: ${newComment.value}`
    });

    // TODO: Send to backend API
    // await torService.updateTor(tor.value.tor_id, { status: approvedStatus });

    console.log('TOR Approved:', {
      tor_id: tor.value.tor_id,
      status: approvedStatus,
      message: newComment.value
    });

    // Update local state
    tor.value.status = approvedStatus;
    newComment.value = '';

    alert(`Submitted approved as "${formatStatus(approvedStatus)}"`);
  } catch (error) {
    console.error('Error approving:', error);
    alert('Failed to approve submission');
  } finally {
    isSubmitting.value = false;
  }
};

const rejectSubmission = async () => {
  if (!tor.value) return;
  if (!newComment.value.trim()) {
    alert('Please add a comment before declining');
    return;
  }

  isSubmitting.value = true;
  try {
    const revisionStatus = getRejectionStatus();
    const roleDisplay = getRoleDisplayName();
    const displayStatus = `Needs Revision by ${roleDisplay}`;
    
    // Add rejection comment
    comments.value.push({
      reviewer_name: roleDisplay,
      created_at: new Date().toISOString(),
      status: displayStatus,
      message: `Revision needed: ${newComment.value}`
    });

    // TODO: Send to backend API
    // await torService.updateTor(tor.value.tor_id, { status: revisionStatus });

    console.log('TOR Rejected:', {
      tor_id: tor.value.tor_id,
      status: revisionStatus,
      message: newComment.value
    });

    // Update local state
    tor.value.status = revisionStatus;
    newComment.value = '';

    alert(`Submission marked as "${displayStatus}"`);
  } catch (error) {
    console.error('Error rejecting:', error);
    alert('Failed to reject submission');
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.back();
};

const fetchTOR = async () => {
  isLoading.value = true;
  try {
    const torId = route.params.id as string;
    console.log('ReviewTOR: Fetching TOR with ID:', torId);
    const response = await torService.getTor(parseInt(torId));

    console.log('ReviewTOR: API Response:', response);

    if (response.success && response.data) {
      tor.value = response.data;
      console.log('ReviewTOR: TOR loaded successfully:', tor.value);
      // TODO: Fetch comments from API
    } else {
      errorMessage.value = response.message || 'Failed to load TOR details';
      console.error('ReviewTOR: Failed to load -', errorMessage.value);
    }
  } catch (error: any) {
    errorMessage.value = error.message || 'An error occurred while loading TOR';
    console.error('ReviewTOR: Error -', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  console.log('ReviewTOR: Component mounted');
  fetchTOR();
});
</script>
