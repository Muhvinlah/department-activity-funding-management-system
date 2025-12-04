<template>
  <div class="h-screen xl:grid grid-cols-3 overflow-hidden">
    <!-- Left Side Content -->
    <div class="hidden xl:flex flex-col bg-[#0d7d90] text-[#f6f5f4]">
      <div class="flex place-items-center px-5 py-4">
        <img src="../assets/campus.png" alt="Campus Logo" class="h-16 sm:h-8">
        <div><h2 class="text-base ml-2">Politeknik Negeri Jakarta (PNJ)</h2></div>
      </div>

      <div class="mt-24 self-center">
        <img src="../assets/Logo.png" alt="System Icon" class="h-80">
      </div>

      <div class="text-center">
        <h1 class="text-3xl font-bold">Sistem Manajemen</h1>
        <h2 class="text-3xl font-bold">Pengelolaan Dana</h2>
        <h3 class="text-3xl font-bold">Kegiatan Jurusan</h3>
      </div>
    </div>

    <!-- Right Side Content -->
    <div class="shapedividers_com-478 flex flex-col justify-center items-center bg-[#f6f5f4] col-span-2">
      <h2 class="text-4xl font-semibold mb-8 text-[#0d7d90]">Sign in</h2>
      <form @submit.prevent="handleStandardLogin" class="w-full max-w-md">
        <div class="mb-5">
          <label for="user_id" class="block text-sm font-medium text-[#0d7d90] mb-1">NIM</label>
          <input
            type="text"
            id="user_id"
            v-model="userId"
            placeholder="Masukkan NIM Anda (10 digit, contoh: 2207412014)"
            required
            class="w-full px-4 py-2 border border-[#0d7d90] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#008797] focus:border-transparent transition duration-200"
          />
        </div>
        <div class="mb-8">
          <label for="password" class="block text-sm font-medium text-[#0d7d90] mb-1">Password</label>
          <input
            :type="isPasswordVisible ? 'text' : 'password'"
            id="password"
            v-model="password"
            placeholder="Masukkan kata sandi anda"
            required
            class="w-full px-4 py-2 border border-[#0d7d90] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#008797] focus:border-transparent transition duration-200"
          />
        </div>

        <div class="mb-4">
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full px-6 py-3 bg-[#008797] text-[#f6f5f4] rounded-lg hover:bg-[#3d97a6] focus:outline-none focus:ring-2 focus:ring-[#008797] focus:ring-opacity-50 transition duration-200 disabled:opacity-50 flex items-center justify-center space-x-2"
          >
            <span v-if="!isLoading">Login</span>
            <span v-else>Loading...</span>
            <ion-icon name="log-in-outline" size="large"></ion-icon>
          </button>
        </div>

        <div class="mb-4">
          <button
            type="button"
            @click="handleSsoLogin"
            disabled
            class="w-full px-6 py-3 bg-gray-400 text-gray-700 font-semibold rounded-lg cursor-not-allowed opacity-60 flex items-center justify-center space-x-2 relative group"
            title="SSO Login akan tersedia setelah integrasi disetujui"
          >
            <span>Login dengan SSO</span>
            <ion-icon name="log-in-outline" size="large"></ion-icon>
            
            <!-- Tooltip -->
            <div class="absolute bottom-full mb-2 hidden group-hover:block w-64 p-2 bg-gray-800 text-white text-xs rounded-lg text-center">
              SSO Login akan tersedia setelah integrasi dengan sistem kampus disetujui
              <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
            </div>
          </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
          {{ errorMessage }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '@/stores/authStore';

  const router = useRouter();
  const authStore = useAuthStore();

  // Using camelCase naming convention
  const userId = ref('');
  const password = ref('');
  const isPasswordVisible = ref(false);
  const isLoading = ref(false);
  const errorMessage = ref('');

  async function handleStandardLogin() {
    if (!userId.value || !password.value) {
      errorMessage.value = 'NIM dan password harus diisi';
      return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    try {
      // Call auth store login with user_id (NIM) and password
      await authStore.login({
        user_id: userId.value,
        password: password.value,
      });

      // Redirect based on role
      if (authStore.role === 'mahasiswa') {
        router.push('/app/home');
      } else if (authStore.role && ['sekretaris jurusan', 'admin jurusan', 'ketua jurusan'].includes(authStore.role)) {
        router.push('/app/home');
      } else {
        router.push('/login');
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.message || 'Login gagal. Periksa kembali NIM dan password.';
      console.error('Login error:', error);
    } finally {
      isLoading.value = false;
    }
  }

  function handleSsoLogin() {
    console.log('SSO login clicked - feature disabled pending approval');
  }
</script>