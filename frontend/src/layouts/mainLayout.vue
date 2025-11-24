<template>
    <div class="min-h-screen">
        <div class="bg-[#0d7d90] flex justify-between">
            <div class="flex items-center px-4 py-2">
                <img src="../assets/campus.png" alt="campus logo" class="h-14">
                <h2 class="text-sm ml-2 text-[#f6f5f4]">Politeknik Negeri Jakarta (PNJ)</h2>
            </div>

            <div class="relative inline-block">
                <!-- Dropdown Trigger -->
                <button 
                class="flex gap-2 mx-4 my-2 items-center cursor-pointer transition-all duration-150 hover:bg-black/10"
                @click="toggleDropdown"
                @blur="closeDropdown"
                >
                <span class="text-xl  text-[#f6f5f4]">{{ username }}</span>
                <ion-icon name="person-circle" class="h-16 w-16  text-[#f6f5f4]"></ion-icon>
                </button>

                <!-- Dropdown Menu -->
                <transition name="dropdown">
                    <div v-show="isOpen" class="absolute top-full right-4 mt-2 w-50 p-4 overflow-hidden shadow-md rounded-2xl bg-[#046378] text-[#f6f5f4] z-10">
                        <ul class="space-y-3">
                            <li class="font-medium">
                            <a href="#" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-[#f6f5f4] dropdown-item" @click.prevent="navigateToProfile">
                                <div class="mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                Account
                            </a>
                            </li>
                            <li class="font-medium">
                            <a href="#" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-[#f6f5f4] dropdown-item" @click.prevent="navigateToChangePassword">
                                <div class="mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                Setting
                            </a>
                            </li>
                            <hr class="border-[#f6f5f4]">
                            <li class="font-medium">
                            <a href="#" class="flex items-center transform transition-colors duration-200 border-r-4 border-transparent hover:border-[#d80900] dropdown-item logout" @click.prevent="logout">
                                <div class="mr-3 text-[#d80900]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </div>
                                Logout
                            </a>
                            </li>
                        </ul>
                    </div>
                </transition>
            </div>
        </div>

        <nav class="bg-[#0d7d90] p-5 drop-shadow-md rounded-b-[3rem]">
            <div class="max-w-full mx-auto flex flex-col px-5 sm:flex-row space-y-2 sm:space-y-0 sm:space-x-8">
                <button @click="goToHome"
                class="px-2 py-2 text-[#0d7d90] bg-white rounded-xl font-semibold text-sm md:text-base hover:ring-2 hover:ring-[#F6F5F4]/50">
                halaman utama
                </button>
                <button v-if="canAccessPengajuan" @click="goToPengajuan"
                class="px-2 py-2 text-[#0d7d90] bg-white rounded-xl font-semibold text-sm md:text-base hover:ring-2 hover:ring-[#F6F5F4]/50">
                Pengajuan
                </button>
                <button @click="goToDashboard"
                class="px-2 py-2 text-[#0d7d90] bg-white rounded-xl font-semibold text-sm md:text-base hover:ring-2 hover:ring-[#F6F5F4]/50">
                Dashboard
                </button>
            </div>
        </nav>

        <router-view />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const isOpen = ref(false);
const username = ref('');

// Computed property to check if user can access Pengajuan tab
const canAccessPengajuan = computed(() => {
  const userRole = authStore.role;
  return userRole === 'mahasiswa' || userRole === 'dosen';
});

// Initialize username from auth store
onMounted(() => {
  username.value = authStore.user?.full_name || 'User';
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
  setTimeout(() => {
    isOpen.value = false;
  }, 150);
};

// Temporary debug in MainLayout.vue
const checkRoutes = () => {
  console.log('Available routes:', router.getRoutes());
  console.log('Current route:', router.currentRoute.value);
};

onMounted(() => {
  checkRoutes();
});

const goToHome = () => {
  console.log('Navigating to Home');
  router.push({ name: 'Home' });
};

const goToPengajuan = () => {
  const userRole = authStore.user?.role;
  console.log('Navigating to submissions');
  router.push({ name: 'TOR' });
};

const goToDashboard = () => {
  console.log('Navigating to Dashboard');
  router.push({ name: 'Dashboard' });
};

const navigateToProfile = () => {
  closeDropdown();
  router.push({ name: 'Profile' }); // You'll need to create this route
};

const navigateToChangePassword = () => {
  closeDropdown();
  router.push({ name: 'ChangePassword' }); // You'll need to create this route
};

const logout = () => {
  closeDropdown();
  // Perform logout logic here
  authStore.logout();
  router.push('/login');
};
</script>