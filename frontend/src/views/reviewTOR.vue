<template>
  <div class="min-h-screen bg-[#F6F5F4] py-12 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-[#0D7D90] rounded-2xl shadow-md p-8 mb-8">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h1 class="text-3xl font-bold text-[#f6f5f4] mb-2">Review TOR</h1>
            <p class="text-[#f6f5f4]">Review Term of Reference</p>
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
                type="text"
                :value="formatDateDisplay(tor.start_date)"
                disabled
                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 cursor-not-allowed"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Berakhir
              </label>
              <input
                type="text"
                :value="formatDateDisplay(tor.end_date)"
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
                </div>
                <button
                  @click="downloadFile(file)"
                  class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                >
                  Download
                </button>
              </div>
              
              <!-- PDF Viewer for PDF files -->
              <div v-if="isPDF(file.file_name)" class="mt-4">
                <div v-if="loadingPdfId === file.attach_id" class="text-center py-8">
                  <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#0d7d90]"></div>
                  <p class="mt-2 text-[#0d7d90]">Loading PDF...</p>
                </div>
                
                <div v-else-if="pdfError[file.attach_id]" class="bg-red-50 border border-red-200 rounded p-4 text-center">
                  <p class="text-red-600">Failed to load PDF: {{ pdfError[file.attach_id] }}</p>
                  <button
                    @click="retryLoadPdf(file)"
                    class="mt-2 px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm"
                  >
                    Retry
                  </button>
                </div>
                
                <div v-else-if="pdfUrls[file.attach_id]" class="border rounded-lg overflow-hidden">
                  <!-- PDF Controls -->
                  <div v-if="showPdfControls && totalPages[file.attach_id] > 1" class="bg-gray-50 p-2 flex items-center justify-between text-sm">
                    <div class="flex items-center space-x-4">
                      <button
                        @click="changePdfPage(file.attach_id, (currentPage[file.attach_id] ?? 1) - 1)"
                        :disabled="!canGoPrevious(file.attach_id)"
                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        ← Previous
                      </button>
                      <span class="text-gray-600">
                        Page {{ currentPage[file.attach_id] ?? 1 }} of {{ totalPages[file.attach_id] ?? 1 }}
                      </span>
                      <button
                        @click="changePdfPage(file.attach_id, (currentPage[file.attach_id] ?? 1) + 1)"
                        :disabled="!canGoNext(file.attach_id)"
                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        Next →
                      </button>
                    </div>
                    <div>
                      <button
                        @click="openPdfInNewTab(file)"
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                      >
                        Open in New Tab
                      </button>
                    </div>
                  </div>
                  
                  <!-- PDF Display -->
                  <div class="overflow-auto max-h-[500px]">
                    <VuePdfEmbed
                      v-if="pdfUrls[file.attach_id] && !pdfError[file.attach_id]"
                      :source="pdfUrls[file.attach_id]"
                      :page="currentPage[file.attach_id] ?? 1"
                      :scale="1"
                      @rendered="(rendered) => onPdfRendered(rendered, file)"
                      @error="(error) => onPdfError(error, file)"
                      @password-requested="(callback) => onPasswordRequested(callback, file)"
                      @loaded="(pdf) => onPdfLoaded(pdf, file)"
                      class="mx-auto"
                    />
                  </div>
                </div>
                
                <div v-else class="text-center py-4">
                  <button
                    @click="loadPdfForViewing(file)"
                    class="px-4 py-2 bg-[#0d7d90] text-white rounded-lg hover:bg-[#3d97a6]"
                  >
                    View PDF
                  </button>
                </div>
              </div>

              <!-- Image Viewer for image files -->
              <div v-else-if="isImage(file.file_name)" class="mt-4">
                <div v-if="imageUrls[file.attach_id]" class="border rounded-lg overflow-hidden">
                  <img 
                    :src="imageUrls[file.attach_id]" 
                    :alt="file.file_name"
                    class="w-full max-h-96 object-contain"
                  />
                </div>
                <div v-else class="text-center py-4">
                  <button
                    @click="loadImageForViewing(file)"
                    class="px-4 py-2 bg-[#0d7d90] text-white rounded-lg hover:bg-[#3d97a6]"
                  >
                    View Image
                  </button>
                </div>
              </div>

              <!-- Other file types -->
              <div v-else class="mt-4 text-center py-4 bg-gray-50 rounded">
                <p class="text-gray-600">File type: {{ getFileExtension(file.file_name) }}</p>
                <p class="text-sm text-gray-500 mt-1">Click "Download" to view this file</p>
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
              Komentar <strong>wajib diisi</strong> untuk <strong>penolakan</strong>. Untuk persetujuan, komentar bersifat opsional.
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
            <div v-if="tor?.status === 'approved_by_head'" class="w-full bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
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
            <!-- Edit TOR button when needs revision -->
            <button
              v-if="needsRevision"
              @click="editTOR"
              class="px-6 py-2 bg-[#0d7d90] text-white rounded-lg hover:bg-[#3d97a6]"
            >
              Edit TOR
            </button>
            
            <!-- Create LPJ button when approved by head -->
            <button
              v-if="tor?.status === 'approved_by_head'"
              @click="createLPJ"
              class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
              Buat LPJ
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import torService from '@/services/torService';
import { USER_ROLES, ADMIN_ROLES } from '@/constants/userRoles';
import VuePdfEmbed from 'vue-pdf-embed';

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
    attach_id: number;
    file_name: string;
    file_path: string;
    file_size?: number;
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

interface Comment {
  reviewer_name: string;
  created_at: string;
  status: string;
  message: string;
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const tor = ref<TOR | null>(null);
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const comments = ref<Comment[]>([]);
const newComment = ref('');
const commentStatus = ref('');

// PDF viewing states
const loadingPdfId = ref<number | null>(null);
const pdfError = ref<Record<number, string>>({});
const imageUrls = ref<Record<number, string>>({});
const pdfUrls = ref<Record<number, string>>({});
const pdfLoadState = ref<Record<number, 'idle' | 'loading' | 'loaded' | 'error'>>({});
const currentPage = ref<Record<number, number>>({});
const totalPages = ref<Record<number, number>>({});
const showPdfControls = ref(true);
const isSubmitting = ref(false);

// Check if current user is a reviewer
const isReviewer = computed(() => {
  const role = authStore.role;
  return ADMIN_ROLES.includes(role as any);
});

// Check if submission needs revision
const needsRevision = computed(() => {
  if (!tor.value) return false;
  return tor.value.status === 'needs_revision_by_secretary' ||
         tor.value.status === 'needs_revision_by_admin' ||
         tor.value.status === 'needs_revision_by_head';
});

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
      return 'submitted';
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

// comments date format
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// inputted date format
const formatDateDisplay = (dateString: string) => {
  if (!dateString) return '';
  const datePart = dateString.split('T')[0];
  if (!datePart) return '';
  const [year, month, day] = datePart.split('-');
  return `${day}-${month}-${year}`;
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

const canGoPrevious = (attachId: number) => {
  const current = currentPage.value[attachId] ?? 1;
  return current > 1;
};

const canGoNext = (attachId: number) => {
  const current = currentPage.value[attachId] ?? 1;
  const total = totalPages.value[attachId] ?? 1;
  return current < total;
};

const changePdfPage = (attachId: number, page: number) => {
  const current = currentPage.value[attachId] ?? 1;
  const total = totalPages.value[attachId] ?? 1;
  if (page >= 1 && page <= total) {
    currentPage.value[attachId] = page;
  }
};

const openPdfInNewTab = (file: any) => {
  if (pdfUrls.value[file.attach_id]) {
    window.open(pdfUrls.value[file.attach_id], '_blank');
  }
};

const retryLoadPdf = async (file: any) => {
  pdfError.value[file.attach_id] = '';
  pdfLoadState.value[file.attach_id] = 'idle';
  await loadPdfForViewing(file);
};

// PDF event handlers
const onPdfLoaded = (pdf: any, file: any) => {
  console.log('PDF loaded successfully:', file.file_name);
  const attachId = file.attach_id;
  
  // Get total pages from the PDF document
  if (pdf && pdf.numPages) {
    totalPages.value[attachId] = pdf.numPages;
    console.log(`PDF has ${pdf.numPages} pages`);
  }
  
  // Initialize current page if not set
  if (!currentPage.value[attachId]) {
    currentPage.value[attachId] = 1;
  }
  
  pdfLoadState.value[attachId] = 'loaded';
  loadingPdfId.value = null;
};

const onPdfRendered = (rendered: any, file: any) => {
  console.log('PDF page rendered:', file.file_name);
};

const onPdfError = (error: any, file: any) => {
  console.error('PDF loading error:', error, file.file_name);
  const attachId = file.attach_id;
  pdfError.value[attachId] = error.message || 'Failed to load PDF';
  pdfLoadState.value[attachId] = 'error';
  loadingPdfId.value = null;
};

const onPasswordRequested = (callback: (password: string) => void, file: any) => {
  const password = prompt(`PDF ${file.file_name} is password protected. Enter password:`);
  if (password) {
    callback(password);
  }
};

// File type helpers
const isImage = (fileName: string) => {
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
  return imageExtensions.some(ext => fileName.toLowerCase().endsWith(ext));
};

const getFileExtension = (fileName: string) => {
  return fileName.split('.').pop()?.toUpperCase() || 'UNKNOWN';
};

const loadPdfForViewing = async (file: any) => {
  const attachId = file.attach_id;
  
  if (pdfUrls.value[attachId]) return;
  
  loadingPdfId.value = attachId;
  pdfError.value[attachId] = '';
  pdfLoadState.value[attachId] = 'loading';
  
  // Initialize default values
  currentPage.value[attachId] = 1;
  
  try {
    const authStore = useAuthStore();
    const response = await fetch(`${API_URL}/attachments/download/${attachId}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      pdfUrls.value[attachId] = url;
    } else {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
  } catch (error) {
    console.error('Error loading PDF:', error);
    pdfError.value[attachId] = error instanceof Error ? error.message : 'Unknown error';
    pdfLoadState.value[attachId] = 'error';
  } finally {
    loadingPdfId.value = null;
  }
};

const loadImageForViewing = async (file: any) => {
  if (imageUrls.value[file.attach_id]) return;
  
  try {
    const authStore = useAuthStore();
    const response = await fetch(`${API_URL}/attachments/download/${file.attach_id}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      imageUrls.value[file.attach_id] = url;
    }
  } catch (error) {
    console.error('Error loading image:', error);
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

    const blob = await response.blob();
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

const approveSubmission = async () => {
  if (!tor.value) return;
  
  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const role = authStore.role;
    let response;

    if (role === USER_ROLES.SECRETARY) {
      response = await torService.reviewBySecretary(
        tor.value.tor_id, 
        'approved', 
        newComment.value.trim() || "Disetujui tanpa komentar"
      );
    } else if (role === USER_ROLES.ADMIN) {
      response = await torService.verifyByAdmin(
        tor.value.tor_id, 
        'approved', 
        newComment.value.trim() || "Disetujui tanpa komentar"
      );
    } else if (role === USER_ROLES.HEAD) {
      response = await torService.approveByHead(
        tor.value.tor_id, 
        'approved', 
        newComment.value.trim() || "Disetujui tanpa komentar"
      );
    } else {
      errorMessage.value = 'Anda tidak memiliki izin untuk menyetujui pengajuan ini';
      isSubmitting.value = false;
      return;
    }

    if (response.success) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      successMessage.value = response.message || 'Pengajuan berhasil disetujui';
      newComment.value = '';
      await fetchTOR();
      setTimeout(() => {
        router.push('/app/home');
      }, 3000);
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
  if (!tor.value) return;
  if (!newComment.value.trim()) {
    errorMessage.value = 'Mohon tambahkan komentar sebelum menolak atau meminta revisi';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  try {
    const role = authStore.role;
    let response;

    if (role === USER_ROLES.SECRETARY) {
      response = await torService.reviewBySecretary(tor.value.tor_id, 'request_revision', newComment.value);
    } else if (role === USER_ROLES.ADMIN) {
      response = await torService.verifyByAdmin(tor.value.tor_id, 'request_revision', newComment.value);
    } else if (role === USER_ROLES.HEAD) {
      response = await torService.approveByHead(tor.value.tor_id, 'request_revision', newComment.value);
    } else {
      errorMessage.value = 'Anda tidak memiliki izin untuk menolak pengajuan ini';
      isSubmitting.value = false;
      return;
    }

    if (response.success) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      successMessage.value = response.message || 'Permintaan revisi berhasil dikirim';
      newComment.value = '';
      await fetchTOR();
      setTimeout(() => {
        router.push('/app/home');
      }, 3000);
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

const editTOR = () => {
  if (!tor.value) return;
  router.push(`/app/tor/${tor.value.tor_id}`);
};

const createLPJ = () => {
  if (!tor.value) return;
  router.push({ name: 'LPJ', query: { torId: tor.value.tor_id } });
};

const fetchComments = () => {
  if (!tor.value || !tor.value.approvals) {
    comments.value = [];
    return;
  }
  
  comments.value = tor.value.approvals.map((approval: any) => ({
    reviewer_name: approval.user?.full_name || approval.role?.role_name || 'Unknown Reviewer',
    created_at: approval.created_at,
    status: approval.status,
    message: approval.catatan || ''
  }));
  
  console.log('ReviewTOR: Comments loaded:', comments.value);
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
      
      if (tor.value.attachments) {
        tor.value.attachments.forEach(file => {
          if (isPDF(file.file_name)) {
            pdfLoadState.value[file.attach_id] = 'idle';
          }
        });
      }
      
      fetchComments();
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

// Clean up blob URLs when component is destroyed
onUnmounted(() => {
  Object.values(pdfUrls.value).forEach(url => {
    if (url.startsWith('blob:')) {
      window.URL.revokeObjectURL(url);
    }
  });
  
  Object.values(imageUrls.value).forEach(url => {
    if (url.startsWith('blob:')) {
      window.URL.revokeObjectURL(url);
    }
  });
});

onMounted(() => {
  console.log('ReviewTOR: Component mounted');
  fetchTOR();
});
</script>

<style scoped>
/* Loading spinner */
@keyframes spin {
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}

/* Style the PDF embed component */
:deep(.vue-pdf-embed) {
  margin: 0 auto;
}

/* Style PDF pages */
:deep(.vue-pdf-embed__page) {
  margin-bottom: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>