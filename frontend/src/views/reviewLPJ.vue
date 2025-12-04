<template>
  <div class="min-h-screen bg-[#F6F5F4] py-12 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-[#0D7D90] rounded-2xl shadow-md p-8 mb-8">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h1 class="text-3xl font-bold text-[#f6f5f4] mb-2">Review LPJ</h1>
            <p class="text-[#f6f5f4]">Review Laporan Pertanggungjawaban</p>
          </div>
          <span :class="getStatusBadgeClass(lpj?.status)" class="px-4 py-2 rounded-xl text-sm font-medium">
            {{ formatStatus(lpj?.status) }}
          </span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-8 text-[#0d7d90]">
        Loading LPJ details...
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-[#D80300] border border-[#D80300] rounded-lg p-4 mb-4">
        <p class="text-[#F6F5F4]">✗ {{ errorMessage }}</p>
      </div>

      <!-- Main Content -->
      <div v-else-if="lpj" class="space-y-6">
        <!-- TOR Info Section -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Term of Reference</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Nama Kegiatan</p>
              <p class="font-medium text-gray-800">{{ tor?.activity_name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Periode</p>
              <p class="font-medium text-gray-800">
                {{ tor ? `${formatDate(tor.start_date)} - ${formatDate(tor.end_date)}` : 'N/A' }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Anggaran Diajukan</p>
              <p class="font-medium text-gray-800">{{ tor ? `Rp ${formatCurrency(tor.budget_submitted)}` : 'N/A' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Status TOR</p>
              <p class="font-medium text-gray-800">{{ formatStatus(tor?.status) }}</p>
            </div>
          </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow p-8 space-y-6">
          <!-- Activity Result -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Hasil Kegiatan
            </label>
            <textarea
              :value="lpj.activity_result"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed h-24"
            ></textarea>
          </div>

          <!-- Actual Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Tanggal Pelaksanaan Aktual
            </label>
            <input
              type="date"
              :value="lpj.actual_date"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
            />
          </div>

          <!-- Evaluation -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Evaluasi
            </label>
            <textarea
              :value="lpj.evaluation"
              disabled
              class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed h-24"
            ></textarea>
          </div>

          <!-- Budget Details -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Anggaran Digunakan
              </label>
              <input
                type="text"
                :value="`Rp ${formatCurrency(lpj.budget_used)}`"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Sisa Anggaran
              </label>
              <input
                type="text"
                :value="`Rp ${formatCurrency((tor?.budget_submitted || 0) - lpj.budget_used)}`"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <!-- Supporting Documents -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-xl font-bold text-gray-800 mb-4">Dokumen Pendukung</h2>
          <div v-if="lpj.attachments && lpj.attachments.length > 0" class="space-y-4">
            <div v-for="(file, index) in lpj.attachments" :key="index" class="border border-gray-300 rounded-lg p-4">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-medium text-gray-800">{{ file.file_name }}</p>
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
                  v-if="pdfUrls[file.attach_id]"
                  :src="pdfUrls[file.attach_id]"
                  class="w-full h-full rounded"
                  type="application/pdf"
                ></iframe>
                <div v-else class="flex items-center justify-center h-full text-gray-500">
                  Loading PDF...
                </div>
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
          <div v-else class="text-gray-500 text-center py-4">
            Belum ada komentar dari reviewer
          </div>

          <!-- Add Comment (Only for Reviewers) -->
          <div v-if="isReviewer" class="border-t pt-4">
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

        <!-- Success Message -->
      <div v-if="successMessage" class="mb-4 p-4 bg-[#03D26F] border border-green-200 rounded-lg">
        <p class="text-green-700 font-medium">✓ {{ successMessage }}</p>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-[#D80300] border border-red-200 rounded-lg">
        <p class="text-[#F6F5F4] font-medium">✗ {{ errorMessage }}</p>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 pt-4 border-t border-[#F6F5F4]/20">
          <!-- Reviewer Actions -->
          <template v-if="isReviewer">
            <div v-if="lpj?.status === 'approved_by_head'" class="w-full bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
              <p class="text-blue-700 font-medium">✓ Sudah disetujui oleh Ketua Jurusan. Tidak dapat diubah.</p>
            </div>
            <div v-else class="flex gap-4 w-full justify-end">
              <button
                @click="rejectSubmission"
                :disabled="isSubmitting"
                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting">Processing...</span>
                <span v-else>Tolak</span>
              </button>
              <button
                @click="approveSubmission"
                :disabled="isSubmitting"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting">Processing...</span>
                <span v-else>Setujui</span>
              </button>
            </div>
          </template>

          <!-- User Actions (Students/Lecturers) -->
          <template v-else>
            <!-- Edit LPJ button when needs revision -->
            <button
              v-if="needsRevision"
              @click="editLPJ"
              class="px-6 py-2 bg-[#0d7d90] text-white rounded-lg hover:bg-[#3d97a6]"
            >
              Edit LPJ
            </button>
          </template>

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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import lpjService from '@/services/lpjService';
import torService from '@/services/torService';
import { USER_ROLES, ADMIN_ROLES } from '@/constants/userRoles';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

interface LPJ {
  lpj_id: number;
  tor_id: number;
  activity_result: string;
  actual_date: string;
  evaluation: string;
  budget_used: number;
  status: string;
  attachments: Array<{
    attach_id: number;
    file_name: string;
    file_path: string;
  }>;
  approvals?: Array<{
    user?: {
      full_name: string;
    };
    role?: {
      role_name: string;
    };
    created_at: string;
    status: string;
    catatan: string;
  }>;
}

interface TOR {
  tor_id: number;
  activity_name: string;
  start_date: string;
  end_date: string;
  budget_submitted: number;
  status: string;
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

const lpj = ref<LPJ | null>(null);
const tor = ref<TOR | null>(null);
const isLoading = ref(false);
const successMessage = ref('');
const pdfUrls = ref<Record<number, string>>({});
const errorMessage = ref('');
const comments = ref<Comment[]>([]);
const newComment = ref('');
const commentStatus = ref('');

// Check if current user is a reviewer
const isReviewer = computed(() => {
  const role = authStore.role;
  return ADMIN_ROLES.includes(role as any);
});

// Check if submission needs revision
const needsRevision = computed(() => {
  if (!lpj.value) return false;
  return lpj.value.status === 'needs_revision' ||
         lpj.value.status === 'needs_revision_by_secretary' ||
         lpj.value.status === 'needs_revision_by_admin' ||
         lpj.value.status === 'needs_revision_by_head';
});

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

const isPDF = (fileName: string) => {
  return fileName.toLowerCase().endsWith('.pdf');
};

const getStatusBadgeClass = (status?: string) => {
  const statusClasses: Record<string, string> = {
    'submitted': 'bg-blue-100 text-blue-800',
    'reviewed_by_secretary': 'bg-purple-100 text-purple-800',
    'verified_by_admin': 'bg-indigo-100 text-indigo-800',
    'approved_by_head': 'bg-green-100 text-green-800',
    'needs_revision_by_secretary': 'bg-orange-100 text-orange-800',
    'needs_revision_by_admin': 'bg-orange-100 text-orange-800',
    'needs_revision_by_head': 'bg-orange-100 text-orange-800',
    'rejected': 'bg-red-100 text-red-800'
  };
  return statusClasses[status || ''] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status?: string) => {
  if (!status) return 'Unknown';
  
  const statusMap: Record<string, string> = {
    'submitted': 'Ditinjau Sekretaris',
    'reviewed_by_secretary': 'Diverifikasi Admin',
    'verified_by_admin': 'Ditinjau Ketua Jurusan',
    'approved_by_head': 'Disetujui Ketua Jurusan',
    'needs_revision_by_secretary': 'Perlu Revisi (Sekretaris)',
    'needs_revision_by_admin': 'Perlu Revisi (Admin)',
    'needs_revision_by_head': 'Perlu Revisi (Ketua Jurusan)',
    'rejected': 'Ditolak'
  };
  return statusMap[status] || status;
};

const loadPdf = async (file: any) => {
  if (pdfUrls.value[file.attach_id]) return;

  try {
    const authStore = useAuthStore();
    const response = await fetch(`${API_URL}/attachments/download/${file.attach_id}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (response.ok) {
      const blob = await response.blob();
      pdfUrls.value[file.attach_id] = window.URL.createObjectURL(blob);
    }
  } catch (error) {
    console.error('Error loading PDF:', error);
  }
};

const downloadFile = async (file: any) => {
  try {
    const authStore = useAuthStore();
    const response = await fetch(`${API_URL}/attachments/download/${file.attach_id}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (!response.ok) {
      console.error('Failed to download file');
      return;
    }

    // Get the blob from response
    const blob = await response.blob();
    
    // Create download link
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = file.file_name || 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading file:', error);
  }
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
    lpj_id: lpj.value?.lpj_id,
    status: commentStatus.value,
    message: newComment.value
  });
};

const approveSubmission = async () => {
  if (!lpj.value) return;
  if (!newComment.value.trim()) {
    errorMessage.value = 'Mohon tambahkan komentar sebelum menyetujui';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const role = authStore.role;
    let response;

    // Call appropriate API based on role
    if (role === USER_ROLES.SECRETARY) {
      response = await lpjService.reviewBySecretary(lpj.value.lpj_id, 'approved', newComment.value);
    } else if (role === USER_ROLES.ADMIN) {
      response = await lpjService.verifyByAdmin(lpj.value.lpj_id, 'approved', newComment.value);
    } else if (role === USER_ROLES.HEAD) {
      response = await lpjService.approveByHead(lpj.value.lpj_id, 'approved', newComment.value);
    } else {
      errorMessage.value = 'Anda tidak memiliki izin untuk menyetujui pengajuan ini';
      isSubmitting.value = false;
      return;
    }

    if (response.success) {
      // Show success message
      successMessage.value = response.message || 'Pengajuan berhasil disetujui';
      newComment.value = '';
      // Re-fetch LPJ data to show updated status and comments
      await fetchLPJ();
      // Navigate back to home after a brief delay
      setTimeout(() => {
        router.push('/app/home');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Gagal menyetujui pengajuan';
    }
  } catch (error) {
    console.error('Error approving:', error);
    errorMessage.value = 'Terjadi kesalahan saat menyetujui pengajuan';
  } finally {
    isSubmitting.value = false;
  }
};

const rejectSubmission = async () => {
  if (!lpj.value) return;
  if (!newComment.value.trim()) {
    errorMessage.value = 'Mohon tambahkan komentar sebelum menolak';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const role = authStore.role;
    let response;

    // Call appropriate API based on role
    if (role === USER_ROLES.SECRETARY) {
      response = await lpjService.reviewBySecretary(lpj.value.lpj_id, 'request_revision', newComment.value);
    } else if (role === USER_ROLES.ADMIN) {
      response = await lpjService.verifyByAdmin(lpj.value.lpj_id, 'request_revision', newComment.value);
    } else if (role === USER_ROLES.HEAD) {
      response = await lpjService.approveByHead(lpj.value.lpj_id, 'request_revision', newComment.value);
    } else {
      errorMessage.value = 'Anda tidak memiliki izin untuk menolak pengajuan ini';
      isSubmitting.value = false;
      return;
    }

    if (response.success) {
      // Show success message
      successMessage.value = response.message || 'Permintaan revisi berhasil dikirim';
      newComment.value = '';
      // Re-fetch LPJ data to show updated status and comments
      await fetchLPJ();
      // Navigate back to home after a brief delay
      setTimeout(() => {
        router.push('/app/home');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Gagal meminta revisi';
    }
  } catch (error) {
    console.error('Error rejecting:', error);
    errorMessage.value = 'Terjadi kesalahan saat meminta revisi';
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.back();
};

const editLPJ = () => {
  if (!lpj.value) return;
  router.push(`/app/lpj/${lpj.value.lpj_id}`);
};

const fetchComments = () => {
  if (!lpj.value || !lpj.value.approvals) {
    comments.value = [];
    return;
  }
  
  // Extract approval records and map them to comments
  comments.value = lpj.value.approvals.map((approval: any) => ({
    reviewer_name: approval.user?.full_name || approval.role?.role_name || 'Unknown Reviewer',
    created_at: approval.created_at,
    status: approval.status,
    message: approval.catatan || ''
  }));
  
  console.log('ReviewLPJ: Comments loaded:', comments.value);
};

const fetchLPJ = async () => {
  isLoading.value = true;
  try {
    const lpjId = route.params.id as string;
    const lpjResponse = await lpjService.getLpj(parseInt(lpjId));

    if (lpjResponse.success && lpjResponse.data) {
      lpj.value = lpjResponse.data;
      console.log('ReviewLPJ: LPJ loaded successfully:', lpj.value);
      if (lpj.value) {
        console.log('ReviewLPJ: Attachments:', lpj.value.attachments);

        // Load PDFs
        if (lpj.value.attachments) {
          lpj.value.attachments.forEach(file => {
            if (isPDF(file.file_name)) {
              loadPdf(file);
            }
          });
        }
      }

      // Fetch related TOR
      if (lpj.value && lpj.value.tor_id) {
        const torResponse = await torService.getTor(lpj.value.tor_id);
        if (torResponse.success && torResponse.data) {
          tor.value = torResponse.data;
        }
      }
      // Fetch comments from the approval records
      fetchComments();
    } else {
      errorMessage.value = lpjResponse.message || 'Failed to load LPJ details';
    }
  } catch (error: any) {
    errorMessage.value = error.message || 'An error occurred while loading LPJ';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchLPJ();
});
</script>
