<template>
  <div class="min-h-screen bg-[#F6F5F4] py-12 px-4">
    <div class="max-w-2xl mx-auto bg-[#0D7D90] rounded-2xl shadow-md p-8 flex flex-col items-center">
      <h1 class="text-3xl font-bold text-[#f6f5f4] mb-2">{{ isEditing ? 'Edit TOR' : 'Pengajuan TOR' }}</h1>
      <p class="text-[#f6f5f4] mb-8">{{ isEditing ? 'Silakan perbarui data TOR di bawah ini' : 'Silakan lengkapi form di bawah untuk mengajukan TOR baru' }}</p>

      <!-- Success Message -->
      <div v-if="successMessage" class="mb-4 p-4 bg-[#03D26F] border border-green-200 rounded-lg">
        <p class="text-green-700">✓ {{ successMessage }}</p>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-[#D80300] border border-[#D80300] rounded-lg">
        <p class="text-[#F6F5F4]">✗ {{ errorMessage }}</p>
      </div>

      <form @submit.prevent="handleSubmit" novalidate class="space-y-6">
        <!-- Activity Name -->
        <div>
          <label for="activity_name" class="block text-sm font-medium text-[#F6F5F4] mb-1">
            Nama Kegiatan <span class="text-[#D80300]">*</span>
          </label>
          <input
            id="activity_name"
            v-model="form.activity_name"
            @blur="validateField('activity_name')"
            type="text"
            class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
            :class="errors.activity_name ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            placeholder="Contoh: Seminar Teknologi"
          />
          <p v-if="errors.activity_name" class="mt-1 text-xs text-[#D80300]">
            {{ errors.activity_name[0] }}
          </p>
        </div>

        <!-- Activity Background -->
        <div>
          <label for="activity_background" class="block text-sm font-medium text-[#F6F5F4] mb-1">
            Latar Belakang Kegiatan <span class="text-red-500">*</span>
          </label>
          <textarea
            id="activity_background"
            v-model="form.activity_background"
            @blur="validateField('activity_background')"
            class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 h-24"
            :class="errors.activity_background ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            placeholder="Jelaskan latar belakang kegiatan ini"
          ></textarea>
          <p v-if="errors.activity_background" class="mt-1 text-xs text-[#D80300]">
            {{ errors.activity_background[0] }}
          </p>
        </div>

        <!-- Activity Purpose -->
        <div>
          <label for="activity_purpose" class="block text-sm font-medium text-[#F6F5F4] mb-1">
            Tujuan Kegiatan <span class="text-red-500">*</span>
          </label>
          <textarea
            id="activity_purpose"
            v-model="form.activity_purpose"
            @blur="validateField('activity_purpose')"
            class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 h-24"
            :class="errors.activity_purpose ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            placeholder="Tuliskan tujuan dari kegiatan ini"
          ></textarea>
          <p v-if="errors.activity_purpose" class="mt-1 text-xs text-[#D80300]">
            {{ errors.activity_purpose[0] }}
          </p>
        </div>

        <!-- Participant -->
        <div>
          <label for="participant" class="block text-sm font-medium text-[#F6F5F4] mb-1">
            Jumlah Peserta <span class="text-red-500">*</span>
          </label>
          <input
            id="participant"
            v-model.number="form.participant"
            @blur="validateField('participant')"
            type="number"
            min="1"
            class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
            :class="errors.participant ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            placeholder="Masukkan jumlah peserta"
          />
          <p v-if="errors.participant" class="mt-1 text-xs text-[#D80300]">
            {{ errors.participant[0] }}
          </p>
        </div>

        <!-- Start Date -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="start_date" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              Tanggal Mulai <span class="text-red-500">*</span>
            </label>
            <input
              id="start_date"
              v-model="form.start_date"
              @blur="validateField('start_date')"
              type="date"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.start_date ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            />
            <p v-if="errors.start_date" class="mt-1 text-xs text-[#D80300]">
              {{ errors.start_date[0] }}
            </p>
          </div>

          <!-- End Date -->
          <div>
            <label for="end_date" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              Tanggal Selesai <span class="text-red-500">*</span>
            </label>
            <input
              id="end_date"
              v-model="form.end_date"
              @blur="validateField('end_date')"
              type="date"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.end_date ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            />
            <p v-if="errors.end_date" class="mt-1 text-xs text-[#D80300]">
              {{ errors.end_date[0] }}
            </p>
          </div>
        </div>

        <!-- Budget Submitted -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="budget_submitted" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              Anggaran (Rp) <span class="text-[#D80300]">*</span>
            </label>
            <input
              id="budget_submitted"
              v-model.number="form.budget_submitted"
              @blur="validateField('budget_submitted')"
              type="number"
              min="0"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.budget_submitted ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
              placeholder="0"
            />
            <p v-if="errors.budget_submitted" class="mt-1 text-xs text-[#D80300]">
              {{ errors.budget_submitted[0] }}
            </p>
          </div>

          <!-- PIC -->
          <div>
            <label for="pic" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              PIC (Penanggung Jawab) <span class="text-[#D80300]">*</span>
            </label>
            <input
              id="pic"
              v-model="form.pic"
              @blur="validateField('pic')"
              type="text"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.pic ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
              placeholder="Nama Penanggung Jawab"
            />
            <p v-if="errors.pic" class="mt-1 text-xs text-[#D80300]">
              {{ errors.pic[0] }}
            </p>
          </div>
        </div>

        <!-- Category -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label for="category_id" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              Kategori Kegiatan <span class="text-[#D80300]">*</span>
            </label>
            <select
              id="category_id"
              v-model.number="form.category_id"
              @blur="validateField('category_id')"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.category_id ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            >
              <option value="">-- Pilih Kategori --</option>
              <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
                {{ cat.category_name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="mt-1 text-xs text-[#D80300]">
              {{ errors.category_id[0] }}
            </p>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="border-t border-[#F6F5F4] pt-6">
          <h3 class="text-lg font-semibold text-[#F6F5F4] mb-4">File Pendukung</h3>

          <!-- RAB Upload -->
          <div class="mb-4">
            <label for="rab_file" class="block text-sm font-medium text-[#F6F5F4] mb-2">
              Rencana Anggaran Belanja (RAB) <span class="text-[#D80300]">*</span>
            </label>
            <input
              id="rab_file"
              type="file"
              accept=".xlsx,.xls,.pdf"
              @change="handleFileUpload('rab', $event)"
              class="w-full px-4 py-2 bg-[#F6F5F4] border border-[#F6F5F4] rounded-lg text-[#0D7D90] file:bg-[#0D7D90] file:text-[#F6F5F4] file:border-none file:px-3 file:py-1 file:rounded file:cursor-pointer"
            />
            <p class="mt-1 text-xs text-[#F6F5F4]">Format: XLSX, XLS, PDF (max 25MB)</p>
            <p v-if="attachments.rab" class="mt-1 text-xs text-[#03D26F]">✓ {{ attachments.rab.name }}</p>
          </div>

          <!-- Supporting Documents -->
          <div>
            <label for="supporting_file" class="block text-sm font-medium text-[#F6F5F4] mb-2">
              Dokumen Pendukung Lainnya
            </label>
            <input
              id="supporting_file"
              type="file"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
              @change="handleFileUpload('supporting', $event)"
              class="w-full px-4 py-2 bg-[#F6F5F4] border border-[#F6F5F4] rounded-lg text-[#0D7D90] file:bg-[#0D7D90] file:text-[#F6F5F4] file:border-none file:px-3 file:py-1 file:rounded file:cursor-pointer"
            />
            <p class="mt-1 text-xs text-[#F6F5F4]">Format: PDF, DOC, DOCX, JPG, PNG (max 25MB)</p>
            <p v-if="attachments.supporting" class="mt-1 text-xs text-[#03D26F]">✓ {{ attachments.supporting.name }}</p>
          </div>
        </div>



        <!-- Submit Button -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 text-[#F6F5F4] border border-[#F6F5F4] rounded-xl hover:bg-[#03D26F]/75 transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">{{ isEditing ? 'Simpan Perubahan' : 'Simpan Pengajuan' }}</span>
            <span v-else>Menyimpan...</span>
          </button>
          <router-link
            to="/app/home"
            class="flex-1 px-4 py-2 text-[#F6F5F4] border border-[#F6F5F4] rounded-xl hover:bg-[#D80300]/75 hover:text-[#F6F5F4] text-center transition-colors"
          >
            Batal
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import torService from '@/services/torService';
import type { TorData } from '@/services/torService';

const router = useRouter();
const route = useRoute();
const isEditing = computed(() => !!route.params.id);
const torId = ref<number | null>(null);

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const form = ref<TorData>({
  activity_name: '',
  activity_background: '',
  activity_purpose: '',
  participant: '',
  start_date: '',
  end_date: '',
  budget_submitted: 0,
  pic: '',
  category_id: 0,
});

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const categories = ref<any[]>([]);
const attachments = ref<Record<string, File | null>>({
  rab: null,
  supporting: null,
});


const validateField = (field: keyof TorData) => {
  // Clear previous errors for this field
  errors.value[field] = [];

  const value = form.value[field];

  // Add your custom validation rules here
  if (field === 'activity_name' && !value) {
    errors.value[field] = ['Nama kegiatan harus diisi'];
  }
  if (field === 'participant' && (value as number) < 1) {
    errors.value[field] = ['Jumlah peserta harus minimal 1'];
  }
  if (field === 'budget_submitted' && (value as number) < 0) {
    errors.value[field] = ['Anggaran harus lebih besar dari 0'];
  }
  if ((field === 'start_date' || field === 'end_date') && form.value.start_date && form.value.end_date) {
    if (new Date(form.value.start_date) > new Date(form.value.end_date)) {
      errors.value['end_date'] = ['Tanggal selesai harus setelah tanggal mulai'];
    }
  }
};

const handleFileUpload = (fieldName: string, event: Event) => {
  const input = event.target as HTMLInputElement;
  const maxFileSize = 25 * 1024 * 1024; // 25 MB

  if (input.files && input.files[0]) {
    const file = input.files[0];
    
    if (file.size > maxFileSize) {
      errorMessage.value = `Ukuran file ${file.name} melebihi batas maksimum 25MB.`;
      input.value = ''; // Clear the input
      attachments.value[fieldName] = null;
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    // Clear error message if successful
    errorMessage.value = '';
    attachments.value[fieldName] = file;
  }
};

const handleSubmit = async () => {
  // Validate all fields before submit
  Object.keys(form.value).forEach((field) => {
    validateField(field as keyof TorData);
  });

  // Check if there are any errors
  const hasErrors = Object.values(errors.value).some((err) => err.length > 0);
  if (hasErrors) {
    errorMessage.value = 'Mohon periksa kembali form Anda';
    return;
  }

  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    let response;
    if (isEditing.value && torId.value) {
      // Create FormData for update
      const formData = new FormData();
      Object.entries(form.value).forEach(([key, value]) => {
        formData.append(key, String(value));
      });
      
      // Append files if they exist
      if (attachments.value.rab) {
        formData.append('rab_file', attachments.value.rab);
      }
      if (attachments.value.supporting) {
        formData.append('supporting_file', attachments.value.supporting);
      }

      response = await torService.updateTor(torId.value, formData);
    } else {
      // Step 1: Create TOR with JSON data (no files)
      response = await torService.createTor(form.value);
      
      // Step 2: Upload files if TOR creation was successful
      if (response.success && response.data) {
        const createdTorId = response.data.tor_id;
        let uploadFailed = false;
        let failureMessage = '';
        
        // Upload RAB file (REQUIRED)
        if (attachments.value.rab) {
          const rabUpload = await torService.uploadAttachment(createdTorId, attachments.value.rab, 'rab');
          if (!rabUpload.success) {
            console.error('Failed to upload RAB file:', rabUpload.message);
            uploadFailed = true;
            failureMessage = `Gagal mengunggah file RAB: ${rabUpload.message || 'File mungkin rusak atau tidak valid'}`;
          }
        }
        
        // Upload supporting file if exists (OPTIONAL)
        if (!uploadFailed && attachments.value.supporting) {
          const supportingUpload = await torService.uploadAttachment(createdTorId, attachments.value.supporting, 'supporting');
          if (!supportingUpload.success) {
            console.error('Failed to upload supporting file:', supportingUpload.message);
            // Supporting file is optional, so we just log the error but don't fail
          }
        }
        
        // If file upload failed, delete the TOR and mark submission as failed
        if (uploadFailed) {
          try {
            await torService.deleteTor(createdTorId);
            console.log('TOR deleted due to file upload failure');
          } catch (deleteError) {
            console.error('Failed to delete TOR after upload failure:', deleteError);
          }
          
          // Mark the response as failed
          response = {
            success: false,
            message: failureMessage
          };
        }
      }
    }

    if (response.success) {
      window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top to show success message
      successMessage.value = response.message || (isEditing.value ? 'TOR berhasil diperbarui!' : 'TOR berhasil dibuat!');
      setTimeout(() => {
        router.push('/app/home');
      }, 3000);
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' }); // Scroll to top to show error message
      if (response.errors) {
        errors.value = response.errors;
        errorMessage.value = 'Ada kesalahan pada form. Silakan periksa kembali.';
      } else {
        errorMessage.value = response.message || (isEditing.value ? 'Terjadi kesalahan saat memperbarui TOR' : 'Terjadi kesalahan saat membuat TOR');
      }
    }
  } catch (error) {
    errorMessage.value = 'Terjadi kesalahan jaringan';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  // Fetch categories from database
  try {
    const authStore = useAuthStore();
    const response = await fetch(`${API_URL}/activity-categories`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    
    if (response.ok) {
      const result = await response.json();
      console.log('Categories fetched:', result); // Debug log
      if (result.success && result.data) {
        categories.value = result.data.map((cat: any) => ({
          category_id: cat.category_id,
          category_name: cat.category_def
        }));
        console.log('Categories mapped:', categories.value); // Debug log
      }
    } else {
      console.error('Failed to fetch categories, status:', response.status);
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error);
    // Fallback to empty array
    categories.value = [];
  }
  
  // Check if editing
  if (route.params.id) {
    torId.value = parseInt(route.params.id as string);
    loading.value = true;
    try {
      const response = await torService.getTor(torId.value);
      if (response.success && response.data) {
        const data = response.data;
        form.value = {
          activity_name: data.activity_name,
          activity_background: data.activity_background,
          activity_purpose: data.activity_purpose,
          participant: data.participant,
          start_date: data.start_date,
          end_date: data.end_date,
          budget_submitted: data.budget_submitted,
          pic: data.pic,
          category_id: data.category_id,
        };
        // Note: Files cannot be pre-populated in file inputs for security reasons
        // We could show existing file names if needed
      } else {
        errorMessage.value = 'Gagal memuat data TOR';
      }
    } catch (error) {
      errorMessage.value = 'Terjadi kesalahan saat memuat data';
    } finally {
      loading.value = false;
    }
  }
});
</script>