<template>
  <div class="min-h-screen py-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="rounded-2xl overflow-hidden mb-6">
        <div class="bg-[#0d7d90] text-[#f6f5f4] p-6">
            <h1 class="text-2xl font-bold text-center">Pengajuan Laporan Pertanggungjawaban (LPJ)</h1>
            <p class="text-center mt-2">Berdasarkan TOR yang telah disetujui</p>
        </div>
        </div>

        <!-- Main Content -->
        <form @submit.prevent="handleSubmit" class="bg-[#0D7D90] rounded-2xl p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - TOR Information -->
            <div class="lg:col-span-1 bg-[#008797]">
                <div class="rounded-2xl p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-[#F6F5F4] mb-4">Informasi TOR</h2>

                    <!-- TOR Selection -->
                    <div class="mb-6">
                    <label class="block text-sm font-medium text-[#F6F5F4] mb-2">
                        Pilih TOR yang Disetujui
                    </label>
                    <select 
                        id="tor_id"
                        v-model.number="form.tor_id"
                        @blur="validateField('tor_id')"
                        @change="validateField('tor_id')"
                        class="w-full p-3 text-[#0D7D90] bg-[#F6F5F4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 focus:border-transparent"
                    >
                        <option value="">-- Pilih TOR --</option>
                        <option 
                        v-for="tor in approvedTors" 
                        :key="tor.tor_id" 
                        :value="tor.tor_id"
                        >
                        {{ tor.activity_name }} (Tahun {{ tor.start_date?.split('-')[0] }})
                        </option>
                    </select>
                    <p v-if="errors.tor_id" class="mt-1 text-xs text-red-600">
                        {{ errors.tor_id[0] }}
                    </p>
                    </div>

                    <!-- TOR Details -->
                    <div v-if="selectedTor" class="space-y-4 pt-4 border-t-2 text-[#F6F5F4]">
                    <div>
                        <h3 class="text-sm font-medium text-[#F6F5F4]">Nama Kegiatan</h3>
                        <p class="text-sm text-[#F6F5F4]">{{ selectedTor.activity_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#F6F5F4]">Tujuan</h3>
                        <p class="text-sm text-[#F6F5F4]">{{ selectedTor.activity_purpose }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#F6F5F4]">Anggaran Disetujui</h3>
                        <p class="text-sm text-[#F6F5F4]">Rp {{ formatCurrency(selectedTor.budget_submitted) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#F6F5F4]">Jadwal Rencana</h3>
                        <p class="text-sm text-[#F6F5F4]">{{ formatDate(selectedTor.start_date) }}</p>
                        <p class="text-sm text-[#F6F5F4]">{{ formatDate(selectedTor.end_date) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-[#F6F5F4]">Peserta Rencana</h3>
                        <p class="text-sm text-[#F6F5F4]">{{ selectedTor.participant }}</p>
                    </div>
                    </div>
                </div>
            </div>

            <!-- right column - lpj form -->
            <div class="lg:col-span-2">
                <!-- Section 1: Hasil Kegiatan -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-[#F6F5F4] mb-4 border-b pb-2">
                        1. Hasil Pelaksanaan Kegiatan
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="tanggalPelaksanaan" class="block text-sm font-medium text-[#F6F5F4] mb-2">
                            Tanggal Pelaksanaan Aktual
                            </label>
                            <input 
                            id="actual_date"
                            type="date"
                            v-model="form.actual_date"
                            class="w-full p-3 text-[#0D7D90] bg-[#F6F5F4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 focus:border-transparent"
                            >
                        </div>
                        <div>
                            <label for="deskripsiHasil" class="block text-sm font-medium text-[#F6F5F4] mb-2">
                                Deskripsi Hasil Kegiatan
                            </label>
                            <textarea 
                            id="activity_result" 
                            v-model="form.activity_result"
                            @blur="validateField('activity_result')"
                            rows="4"
                            placeholder="Jelaskan secara detail hasil yang dicapai dari kegiatan ini"
                            class="w-full p-3 text-[#0D7D90] bg-[#F6F5F4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 focus:border-transparent"
                            ></textarea>
                        </div>
                    </div>
                </div>

            <!-- Section 2: Realisasi Anggaran -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-[#F6F5F4] mb-4 border-b pb-2">
                    2. Realisasi Anggaran
                </h2>
                
                <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-[#008797] p-4 rounded-xl shadow-lg">
                        <h3 class="text-sm font-medium text-[#F6F5F4] mb-2">Anggaran Disetujui</h3>
                        <p class="text-lg font-bold text-[#F6F5F4]" v-if="selectedTor">
                        Rp {{ selectedTor.budget_submitted?.toLocaleString('id-ID') }}
                        </p>
                        <p class="text-sm text-blue-600" v-else>-</p>
                    </div>
                
                    <div>
                        <label for="realisasiAnggaran" class="block text-sm font-medium text-[#F6F5F4] mb-2">
                            Realisasi Anggaran
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                              <span class="text-[#0D7D90]">Rp</span>
                            </div>
                            <input 
                            id="budget_used"
                            v-model="form.budget_used"
                            @blur="validateField('budget_used')"
                            type="number"
                            min="1"
                            placeholder="0"
                            class="w-full pl-12 p-3 text-[#0D7D90] bg-[#F6F5F4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 focus:border-transparent"
                            >
                        </div>
                    </div>
                </div>

                <div v-if="selectedTor && form.budget_used" class="p-4 rounded-xl shadow-lg bg-[#008797]">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-[#F6F5F4]">Selisih Anggaran:</span>
                        <span 
                        :class="{
                            'text-green-600 font-bold': getBudgetDifference() >= 0,
                            'text-red-600 font-bold': getBudgetDifference() < 0
                        }"
                        >
                            Rp {{ formatCurrency(getBudgetDifference()) }}
                            ({{ getBudgetDifferencePercentage() }}%)
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Dokumentasi & Bukti -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-[#F6F5F4] mb-4 border-b pb-2">
                3. Dokumentasi & Bukti Pendukung
                </h2>
                <div class="space-y-6">
                    <!-- Foto Kegiatan -->
                    <div>
                        <label class="block text-sm font-medium text-[#F6F5F4] mb-2">
                            Foto Dokumentasi Kegiatan
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <label class="block text-sm font-medium text-[#F6F5F4] mb-2">Foto Dokumentasi (maks 4)</label>
                            <input type="file" accept="image/*" multiple @change="handleFileUpload('photos', $event)" class="w-full p-2 text-[#0D7D90] bg-[#F6F5F4] rounded-xl" />
                            <p v-if="attachments.photos && attachments.photos.length" class="mt-2 text-xs text-[#F6F5F4]">{{ attachments.photos.length }} file(s) dipilih</p>
                          </div>
                          <div>
                            <label class="block text-sm font-medium text-[#F6F5F4] mb-2">Foto Dokumentasi Tambahan (opsional)</label>
                            <input type="file" accept="image/*" multiple @change="handleFileUpload('photos_additional', $event)" class="w-full p-2 text-[#0D7D90] bg-[#F6F5F4] rounded-xl" />
                            <p v-if="attachments.photos_additional && attachments.photos_additional.length" class="mt-2 text-xs text-[#F6F5F4]">{{ attachments.photos_additional.length }} file(s) dipilih</p>
                          </div>
                        </div>
                    </div>

                    <!-- Bukti Pendukung -->
                    <div>
                        <label class="block text-sm font-medium text-[#F6F5F4] mb-2">
                            Dokumen Pendukung
                        </label>
                        <div class="space-y-4">
                        <div>
                          <label class="block text-sm font-medium text-[#F6F5F4] mb-2">Daftar Hadir Peserta</label>
                          <input type="file" accept=".pdf,.doc,.docx" @change="handleFileUpload('daftar_hadir', $event)" class="w-full p-2 text-[#0D7D90] bg-[#F6F5F4] rounded-xl" />
                          <p v-if="attachments.daftar_hadir" class="mt-1 text-xs text-[#F6F5F4]">✓ {{ attachments.daftar_hadir.name }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-[#F6F5F4] mb-2">Kwitansi & Bukti Pengeluaran</label>
                          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileUpload('bukti_pengeluaran', $event)" class="w-full p-2 text-[#0D7D90] bg-[#F6F5F4] rounded-xl" />
                          <p v-if="attachments.bukti_pengeluaran" class="mt-1 text-xs text-[#F6F5F4]">✓ {{ attachments.bukti_pengeluaran.name }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-[#F6F5F4] mb-2">Dokumen Pendukung Lainnya</label>
                          <input type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="handleFileUpload('dokumen_lainnya', $event)" class="w-full p-2 text-[#0D7D90] bg-[#F6F5F4] rounded-xl" />
                          <p v-if="attachments.dokumen_lainnya" class="mt-1 text-xs text-[#F6F5F4]">✓ {{ attachments.dokumen_lainnya.name }}</p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Evaluasi & Penutup -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-[#F6F5F4] mb-4 border-b pb-2">
                4. Evaluasi & Penutup
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="evaluasiKegiatan" class="block text-sm font-medium text-[#F6F5F4] mb-2">
                        Evaluasi Kegiatan
                        </label>
                        <textarea 
                        id="activity_evaluation"
                        v-model="form.activity_evaluation"
                        @blur="validateField('activity_evaluation')"
                        rows="4"
                        placeholder="Jelaskan evaluasi terhadap pelaksanaan kegiatan (keberhasilan, kendala, pembelajaran)"
                        class="w-full p-3 text-[#0D7D90] bg-[#F6F5F4] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 focus:border-transparent"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-between space-x-5 pt-6 border-t">
                <button 
                type="submit"
                class="px-8 py-3 text-[#F6F5F4] border border-[#F6F5F4] rounded-xl hover:bg-[#03D26F] transition-colors focus:outline-none focus:ring-2 focus:ring-[#F6F5F4] disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="!loading">Simpan Pengajuan LPJ</span>
                    <span v-else>Menyimpan...</span>
                Ajukan LPJ
                </button>
                <router-link
                to="/app/home"
                class="flex-1 px-4 py-2 border border-[#F6F5F4] text-[#F6F5F4] rounded-xl hover:bg-[#D80300] hover:text-[#F6F5F4] text-center transition-colors"
                >
                Batal
                </router-link>
            </div>
            </div>
        </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import lpjService from '@/services/lpjService';
import torService from '@/services/torService';
import type { LpjData } from '@/services/lpjService';

const router = useRouter();

const form = ref<LpjData>({
  tor_id: 0,
  activity_result: '',
  activity_evaluation: '',
  actual_date: '',
  budget_used: 0,
});

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const approvedTors = ref<any[]>([]);
const attachments = ref<{
  photos: File[] | null;
  photos_additional: File[] | null;
  daftar_hadir: File | null;
  bukti_pengeluaran: File | null;
  dokumen_lainnya: File | null;
}>({
  photos: null,
  photos_additional: null,
  daftar_hadir: null,
  bukti_pengeluaran: null,
  dokumen_lainnya: null,
});

const selectedTor = computed(() => {
  return approvedTors.value.find((t) => t.tor_id === form.value.tor_id);
});

const validateField = (field: keyof LpjData) => {
  // Clear previous errors for this field
  errors.value[field] = [];

  const value = form.value[field];

  // Add your custom validation rules here
  if (field === 'tor_id' && !value) {
    errors.value[field] = ['Silakan pilih TOR'];
  }
  if (field === 'activity_result' && !value) {
    errors.value[field] = ['Hasil kegiatan harus diisi'];
  }
  if (field === 'activity_evaluation' && !value) {
    errors.value[field] = ['Evaluasi kegiatan harus diisi'];
  }
  if (field === 'budget_used' && (value as number) < 0) {
    errors.value[field] = ['Anggaran harus lebih besar dari atau sama dengan 0'];
  }
  // Ensure TOR is selected
  if (field === 'tor_id' && !form.value.tor_id) {
    errors.value[field] = ['Silakan pilih TOR yang disetujui'];
  }
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const formatDate = (dateString: string) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getBudgetDifference = () => {
  if (!selectedTor.value || !form.value.budget_used) return 0;
  return selectedTor.value.budget_submitted - form.value.budget_used;
};

const getBudgetDifferencePercentage = () => {
  if (!selectedTor.value || !form.value.budget_used) return '0';
  const difference = getBudgetDifference();
  const percentage = (difference / selectedTor.value.budget_submitted) * 100;
  return percentage.toFixed(1);
};

const handleSubmit = async () => {
  // Validate all fields before submit
  Object.keys(form.value).forEach((field) => {
    validateField(field as keyof LpjData);
  });

  // Check if there are any errors
  const hasErrors = Object.values(errors.value).some((err) => err.length > 0);
  if (hasErrors) {
    errorMessage.value = 'Mohon periksa kembali form Anda';
    return;
  }

  // Ensure selected TOR exists and is approved
  if (!selectedTor.value) {
    errorMessage.value = 'TOR yang dipilih tidak valid atau belum disetujui';
    return;
  }

  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  // If there are file attachments, submit as multipart/form-data
  const hasFiles = (
    (attachments.value.photos && attachments.value.photos.length > 0) ||
    (attachments.value.photos_additional && attachments.value.photos_additional.length > 0) ||
    !!attachments.value.daftar_hadir ||
    !!attachments.value.bukti_pengeluaran ||
    !!attachments.value.dokumen_lainnya
  );

  let response;
  if (hasFiles) {
    const formData = new FormData();
    formData.append('tor_id', String(form.value.tor_id));
    formData.append('activity_result', form.value.activity_result);
    formData.append('activity_evaluation', form.value.activity_evaluation);
    formData.append('actual_date', form.value.actual_date);
    formData.append('budget_used', String(form.value.budget_used));

    // photos (multiple)
    if (attachments.value.photos && Array.isArray(attachments.value.photos)) {
      (attachments.value.photos as File[]).forEach((f) => formData.append('photos[]', f));
    }
    if (attachments.value.photos_additional && Array.isArray(attachments.value.photos_additional)) {
      (attachments.value.photos_additional as File[]).forEach((f) => formData.append('photos_additional[]', f));
    }
    if (attachments.value.daftar_hadir) formData.append('daftar_hadir', attachments.value.daftar_hadir as File);
    if (attachments.value.bukti_pengeluaran) formData.append('bukti_pengeluaran', attachments.value.bukti_pengeluaran as File);
    if (attachments.value.dokumen_lainnya) formData.append('dokumen_lainnya', attachments.value.dokumen_lainnya as File);

    response = await lpjService.createLpjWithFiles(formData);
  } else {
    response = await lpjService.createLpj(form.value);
  }

  loading.value = false;

  if (response.success) {
    successMessage.value = response.message || 'LPJ berhasil dibuat!';
    setTimeout(() => {
      router.push('/app/home');
    }, 1500);
  } else {
    if (response.errors) {
      errors.value = response.errors;
      errorMessage.value = 'Ada kesalahan pada form. Silakan periksa kembali.';
    } else {
      errorMessage.value = response.message || 'Terjadi kesalahan saat membuat LPJ';
    }
  }
};

const handleFileUpload = (fieldName: string, event: Event) => {
  const input = event.target as HTMLInputElement;
  if (!input || !input.files) return;
  const key = fieldName as keyof typeof attachments.value;
  if (input.multiple) {
    const files = Array.from(input.files);
    (attachments.value as any)[key] = files;
  } else {
    (attachments.value as any)[key] = input.files[0] ?? null;
  }
};

onMounted(async () => {
  // Fetch approved TORs from API
  try {
    const response = await torService.getMyApprovedTors();
    if (response.success && response.data) {
      approvedTors.value = response.data;
    } else {
      console.error('Failed to fetch approved TORs:', response.message);
      errorMessage.value = 'Gagal memuat daftar TOR yang disetujui';
    }
  } catch (error) {
    console.error('Error fetching approved TORs:', error);
    errorMessage.value = 'Terjadi kesalahan saat memuat data TOR';
  }
});
</script>