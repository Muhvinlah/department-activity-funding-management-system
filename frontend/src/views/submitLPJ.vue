<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
                <div class="bg-[#0d7d90] text-white p-6">
                    <h1 class="text-2xl font-bold text-center">Pengajuan Laporan Pertanggungjawaban (LPJ)</h1>
                    <p class="text-center text-blue-100 mt-2">Berdasarkan TOR yang telah disetujui</p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - TOR Information -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi TOR</h2>
                        
                        <!-- TOR Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih TOR yang Disetujui
                            </label>
                            <select 
                                v-model="selectedTorId"
                                @change="loadTorData"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                            >
                                <option value="">-- Pilih TOR --</option>
                                <option 
                                v-for="tor in approvedTors" 
                                :key="tor.id" 
                                :value="tor.id"
                                >
                                {{ tor.nama_kegiatan }} ({{ tor.tahun }})
                                </option>
                            </select>
                        </div>

                        <!-- TOR Details -->
                        <div v-if="selectedTor" class="space-y-4 border-t pt-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Nama Kegiatan</h3>
                                <p class="text-sm text-gray-800">{{ selectedTor.nama_kegiatan }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Tujuan</h3>
                                <p class="text-sm text-gray-800">{{ selectedTor.tujuan }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Anggaran Disetujui</h3>
                                <p class="text-sm text-gray-800">Rp {{ formatCurrency(selectedTor.anggaran) }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Jadwal Rencana</h3>
                                <p class="text-sm text-gray-800">{{ formatDate(selectedTor.jadwal) }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Peserta Rencana</h3>
                                <p class="text-sm text-gray-800">{{ selectedTor.peserta }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - LPJ Form -->
                <div class="lg:col-span-2">
                    <form @submit.prevent="submitLPJ" class="bg-white rounded-2xl shadow-lg p-6">
                        <!-- Section 1: Hasil Kegiatan -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                1. Hasil Pelaksanaan Kegiatan
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="tanggalPelaksanaan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tanggal Pelaksanaan Aktual
                                    </label>
                                    <input 
                                        type="date" 
                                        id="tanggalPelaksanaan" 
                                        v-model="form.tanggal_pelaksanaan"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="lokasiKegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lokasi Kegiatan
                                    </label>
                                    <input 
                                        type="text" 
                                        id="lokasiKegiatan" 
                                        v-model="form.lokasi_kegiatan"
                                        placeholder="Tempat dilaksanakannya kegiatan"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="jumlahPesertaAktual" class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah Peserta Aktual
                                    </label>
                                    <input 
                                        type="number" 
                                        id="jumlahPesertaAktual" 
                                        v-model="form.jumlah_peserta_aktual"
                                        placeholder="Jumlah peserta yang hadir"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="deskripsiHasil" class="block text-sm font-medium text-gray-700 mb-2">
                                        Deskripsi Hasil Kegiatan
                                    </label>
                                    <textarea 
                                        id="deskripsiHasil" 
                                        v-model="form.deskripsi_hasil"
                                        rows="4"
                                        placeholder="Jelaskan secara detail hasil yang dicapai dari kegiatan ini"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    ></textarea>
                                </div>

                                <div>
                                    <label for="pencapaianTujuan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Pencapaian Tujuan
                                    </label>
                                    <textarea 
                                        id="pencapaianTujuan" 
                                        v-model="form.pencapaian_tujuan"
                                        rows="3"
                                        placeholder="Jelaskan sejauh mana tujuan kegiatan tercapai"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Realisasi Anggaran -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                2. Realisasi Anggaran
                            </h2>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <h3 class="text-sm font-medium text-blue-800 mb-2">Anggaran Disetujui</h3>
                                        <p class="text-lg font-bold text-blue-900" v-if="selectedTor">
                                        Rp {{ formatCurrency(selectedTor.anggaran) }}
                                        </p>
                                        <p class="text-sm text-blue-600" v-else>-</p>
                                    </div>
                                
                                    <div>
                                        <label for="realisasiAnggaran" class="block text-sm font-medium text-gray-700 mb-2">
                                        Realisasi Anggaran
                                        </label>
                                        <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">Rp</span>
                                        </div>
                                        <input 
                                            type="number" 
                                            id="realisasiAnggaran" 
                                            v-model="form.realisasi_anggaran"
                                            placeholder="0"
                                            class="w-full pl-12 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                        >
                                    </div>
                                </div>
                            </div>

                                <div v-if="selectedTor && form.realisasi_anggaran" class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Selisih Anggaran:</span>
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

                                <div>
                                <label for="rincianPenggunaan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rincian Penggunaan Anggaran
                                </label>
                                <textarea 
                                    id="rincianPenggunaan" 
                                    v-model="form.rincian_penggunaan"
                                    rows="4"
                                    placeholder="Jelaskan detail penggunaan anggaran (item pengeluaran, jumlah, keterangan)"
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Dokumentasi & Bukti -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                3. Dokumentasi & Bukti Pendukung
                            </h2>
                            <div class="space-y-6">
                                <!-- Foto Kegiatan -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Foto Dokumentasi Kegiatan
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <FileUpload 
                                        label="Foto 1 (Utama)"
                                        @file-selected="handleFileUpload('foto_1', $event)"
                                        accept="image/*"
                                        />
                                        <FileUpload 
                                        label="Foto 2"
                                        @file-selected="handleFileUpload('foto_2', $event)"
                                        accept="image/*"
                                        />
                                        <FileUpload 
                                        label="Foto 3"
                                        @file-selected="handleFileUpload('foto_3', $event)"
                                        accept="image/*"
                                        />
                                        <FileUpload 
                                        label="Foto 4"
                                        @file-selected="handleFileUpload('foto_4', $event)"
                                        accept="image/*"
                                        />
                                    </div>
                                </div>

                                <!-- Bukti Pendukung -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Dokumen Pendukung
                                    </label>
                                    <div class="space-y-4">
                                        <FileUpload 
                                        label="Daftar Hadir Peserta"
                                        @file-selected="handleFileUpload('daftar_hadir', $event)"
                                        accept=".pdf,.doc,.docx"
                                        />
                                        <FileUpload 
                                        label="Kwitansi & Bukti Pengeluaran"
                                        @file-selected="handleFileUpload('bukti_pengeluaran', $event)"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        />
                                        <FileUpload 
                                        label="Dokumen Pendukung Lainnya"
                                        @file-selected="handleFileUpload('dokumen_lainnya', $event)"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Evaluasi & Penutup -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                4. Evaluasi & Penutup
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="evaluasiKegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Evaluasi Kegiatan
                                    </label>
                                    <textarea 
                                        id="evaluasiKegiatan" 
                                        v-model="form.evaluasi_kegiatan"
                                        rows="4"
                                        placeholder="Jelaskan evaluasi terhadap pelaksanaan kegiatan (keberhasilan, kendala, pembelajaran)"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    ></textarea>
                                </div>

                                <div>
                                    <label for="rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">
                                        Rekomendasi untuk Kegiatan Mendatang
                                    </label>
                                    <textarea 
                                        id="rekomendasi" 
                                        v-model="form.rekomendasi"
                                        rows="3"
                                        placeholder="Berikan rekomendasi untuk perbaikan kegiatan serupa di masa depan"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-6 border-t">
                            <button 
                                type="submit"
                                :disabled="!isFormValid"
                                class="bg-[#0d7d90] text-white px-8 py-3 rounded-lg hover:bg-[#0a6a7a] transition-colors focus:outline-none focus:ring-2 focus:ring-[#0d7d90] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Ajukan LPJ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Reactive data
const selectedTorId = ref('')
const selectedTor = ref(null)
const form = ref({
  tor_id: '',
  tanggal_pelaksanaan: '',
  lokasi_kegiatan: '',
  jumlah_peserta_aktual: '',
  deskripsi_hasil: '',
  pencapaian_tujuan: '',
  realisasi_anggaran: '',
  rincian_penggunaan: '',
  evaluasi_kegiatan: '',
  rekomendasi: '',
  dokumentasi: {
    foto_1: null,
    foto_2: null,
    foto_3: null,
    foto_4: null,
    daftar_hadir: null,
    bukti_pengeluaran: null,
    dokumen_lainnya: null
  }
})

// Mock data - in real app, this would come from API
const approvedTors = ref([
  {
    id: 1,
    nama_kegiatan: 'Seminar Kewirausahaan 2024',
    tujuan: 'Meningkatkan jiwa kewirausahaan mahasiswa',
    anggaran: 15000000,
    jadwal: '2024-03-15',
    peserta: '100 mahasiswa'
  },
  {
    id: 2,
    nama_kegiatan: 'Pelatihan Public Speaking',
    tujuan: 'Melatih kemampuan public speaking mahasiswa',
    anggaran: 8000000,
    jadwal: '2024-04-10',
    peserta: '50 mahasiswa'
  }
])

// Computed properties
const isFormValid = computed(() => {
  return selectedTorId.value && 
         form.value.tanggal_pelaksanaan && 
         form.value.deskripsi_hasil &&
         form.value.realisasi_anggaran
})

// Methods
const loadTorData = () => {
  const tor = approvedTors.value.find(t => t.id === parseInt(selectedTorId.value))
  selectedTor.value = tor
  form.value.tor_id = selectedTorId.value
}

const handleFileUpload = (fieldName, file) => {
  form.value.dokumentasi[fieldName] = file
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID').format(amount || 0)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getBudgetDifference = () => {
  if (!selectedTor.value || !form.value.realisasi_anggaran) return 0
  return selectedTor.value.anggaran - parseFloat(form.value.realisasi_anggaran)
}

const getBudgetDifferencePercentage = () => {
  if (!selectedTor.value || !form.value.realisasi_anggaran) return '0'
  const difference = getBudgetDifference()
  const percentage = (difference / selectedTor.value.anggaran) * 100
  return percentage.toFixed(1)
}

const submitLPJ = async () => {
  try {
    console.log('Submitting LPJ:', form.value)
    
    // Here you would typically send to your backend
    // await $fetch('/api/lpj', {
    //   method: 'POST',
    //   body: form.value
    // })
    
    // Reset form after successful submission
    // resetForm()
    
    alert('LPJ berhasil diajukan!')
  } catch (error) {
    console.error('Error submitting LPJ:', error)
    alert('Terjadi kesalahan saat mengajukan LPJ')
  }
}

const resetForm = () => {
  selectedTorId.value = ''
  selectedTor.value = null
  form.value = {
    tor_id: '',
    tanggal_pelaksanaan: '',
    lokasi_kegiatan: '',
    jumlah_peserta_aktual: '',
    deskripsi_hasil: '',
    pencapaian_tujuan: '',
    realisasi_anggaran: '',
    rincian_penggunaan: '',
    evaluasi_kegiatan: '',
    rekomendasi: '',
    dokumentasi: {
      foto_1: null,
      foto_2: null,
      foto_3: null,
      foto_4: null,
      daftar_hadir: null,
      bukti_pengeluaran: null,
      dokumen_lainnya: null
    }
  }
}

// Lifecycle
onMounted(() => {
  // In real app, fetch approved TORs from API
  // approvedTors.value = await $fetch('/api/approved-tors')
})
</script>