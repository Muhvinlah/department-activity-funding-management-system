<template>
  <div class="min-h-screen bg-[#F6F5F4] py-12 px-4">
    <div class="max-w-2xl mx-auto bg-[#0D7D90] rounded-2xl shadow-md p-8 flex flex-col items-center">
      <h1 class="text-3xl font-bold text-[#f6f5f4] mb-2">Pengajuan TOR</h1>
      <p class="text-[#f6f5f4] mb-8">Silakan lengkapi form di bawah untuk mengajukan TOR baru</p>

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
            Peserta <span class="text-red-500">*</span>
          </label>
          <textarea
            id="participant"
            v-model="form.participant"
            @blur="validateField('participant')"
            class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50 h-20"
            :class="errors.participant ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            placeholder="Siapa saja peserta kegiatan ini?"
          ></textarea>
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
              placeholder="Nama PIC"
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

          <!-- Budget -->
          <div class="hidden">
            <label for="budget_id" class="block text-sm font-medium text-[#F6F5F4] mb-1">
              Tahun Anggaran <span class="text-[#D80300]">*</span>
            </label>
            <select
              id="budget_id"
              v-model.number="form.budget_id"
              @blur="validateField('budget_id')"
              class="w-full px-4 py-2 bg-[#F6F5F4] border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/50"
              :class="errors.budget_id ? 'border-[#D80300]' : 'border-[#F6F5F4]'"
            >
              <option value="">-- Pilih Tahun Anggaran --</option>
              <option v-for="budget in budgets" :key="budget.budget_id" :value="budget.budget_id">
                {{ budget.year }}
              </option>
            </select>
            <p v-if="errors.budget_id" class="mt-1 text-xs text-[#D80300]">
              {{ errors.budget_id[0] }}
            </p>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-2 text-[#F6F5F4] border border-[#F6F5F4] rounded-xl hover:bg-[#03D26F]/75 transition-colors focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="!loading">Simpan Pengajuan</span>
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
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import torService from '@/services/torService';
import type { TorData } from '@/services/torService';

const router = useRouter();

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
  budget_id: 0,
});

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const categories = ref<any[]>([]);
const budgets = ref<any[]>([]);

const validateField = (field: keyof TorData) => {
  // Clear previous errors for this field
  errors.value[field] = [];

  const value = form.value[field];

  // Add your custom validation rules here
  if (field === 'activity_name' && !value) {
    errors.value[field] = ['Nama kegiatan harus diisi'];
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

  const response = await torService.createTor(form.value);

  loading.value = false;

  if (response.success) {
    successMessage.value = response.message || 'TOR berhasil dibuat!';
    setTimeout(() => {
      router.push('/app/home');
    }, 1500);
  } else {
    if (response.errors) {
      errors.value = response.errors;
      errorMessage.value = 'Ada kesalahan pada form. Silakan periksa kembali.';
    } else {
      errorMessage.value = response.message || 'Terjadi kesalahan saat membuat TOR';
    }
  }
};

onMounted(async () => {
  // Fetch categories and budgets
  // This assumes you have these endpoints available
  // Update with your actual API calls
  categories.value = [
    { category_id: 1, category_name: 'Akademik' },
    { category_id: 2, category_name: 'Kemahasiswaan' },
    { category_id: 3, category_name: 'Penelitian' },
    { category_id: 4, category_name: 'Pengabdian Masyarakat' }
  ];
  budgets.value = [
    { budget_id: 1, year: '2025' },
    { budget_id: 2, year: '2026' },
  ];
    
    // Auto-select tahun anggaran based on start_date
    watch(() => form.value.start_date, (newDate) => {
      if (!newDate) return;
      const year = newDate.split('-')[0];
      const match = budgets.value.find(b => String(b.year) === year);
      if (match) {
        form.value.budget_id = match.budget_id;
      }
    });
});
</script>