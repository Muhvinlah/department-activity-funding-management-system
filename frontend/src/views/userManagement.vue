<template>
  <div class="px-5 py-5">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Manajemen Pengguna</h1>
      <button 
        @click="openCreateModal"
        class="bg-[#0d7d90] hover:bg-[#0a6676] text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition duration-300"
      >
        <ion-icon name="person-add-outline" class="text-xl"></ion-icon>
        Tambah Pengguna
      </button>
    </div>

    <!-- User Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#0d7d90] text-white">
              <th class="p-4 rounded-tl-lg">NIM / User ID</th>
              <th class="p-4">Nama Lengkap</th>
              <th class="p-4">Email</th>
              <th class="p-4">Peran</th>
              <th class="p-4 rounded-tr-lg text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.user_id" class="hover:bg-gray-50 transition duration-150">
              <td class="p-4 font-medium text-gray-900">{{ user.user_id }}</td>
              <td class="p-4 text-gray-700">{{ user.full_name }}</td>
              <td class="p-4 text-gray-600">{{ user.email }}</td>
              <td class="p-4">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  {{ user.role?.role_def || '-' }}
                </span>
              </td>
              <td class="p-4 text-center">
                <div class="flex justify-center space-x-2">
                  <button 
                    @click="openEditModal(user)" 
                    class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition"
                    title="Edit"
                  >
                    <ion-icon name="create-outline" class="text-lg"></ion-icon>
                  </button>
                  <button 
                    @click="confirmDelete(user)" 
                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition"
                    title="Hapus"
                  >
                    <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td colspan="5" class="p-8 text-center text-gray-500">
                Tidak ada data pengguna.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
          <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">
              {{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
              <ion-icon name="close-outline" class="text-2xl"></ion-icon>
            </button>
          </div>
          
          <form @submit.prevent="saveUser" class="p-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIM / User ID</label>
                <input 
                  type="text" 
                  v-model="form.user_id" 
                  :disabled="isEditing"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-[#0d7d90] disabled:bg-gray-100 disabled:text-gray-500"
                  placeholder="Contoh: 2207412014"
                  required
                >
                <p v-if="errors.user_id" class="text-red-500 text-xs mt-1">{{ errors.user_id[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input 
                  type="text" 
                  v-model="form.full_name" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-[#0d7d90]"
                  placeholder="Nama Lengkap"
                  required
                >
                <p v-if="errors.full_name" class="text-red-500 text-xs mt-1">{{ errors.full_name[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input 
                  type="email" 
                  v-model="form.email" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-[#0d7d90]"
                  placeholder="email@contoh.com"
                  required
                >
                 <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peran (Role)</label>
                <select 
                  v-model="form.role_id" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-[#0d7d90]"
                  required
                >
                  <option disabled value="">Pilih Peran</option>
                  <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                    {{ role.role_def }}
                  </option>
                </select>
                <p v-if="errors.role_id" class="text-red-500 text-xs mt-1">{{ errors.role_id[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  {{ isEditing ? 'Password Baru (Opsional)' : 'Password' }}
                </label>
                <input 
                  type="password" 
                  v-model="form.password" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-[#0d7d90]"
                  :placeholder="isEditing ? 'Biarkan kosong jika tidak diubah' : 'Minimal 6 karakter'"
                  :required="!isEditing"
                >
                <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password[0] }}</p>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
              <button 
                type="button" 
                @click="closeModal"
                class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
              >
                Batal
              </button>
              <button 
                type="submit" 
                :disabled="isLoading"
                class="px-5 py-2.5 text-white bg-[#0d7d90] hover:bg-[#0a6676] rounded-lg font-medium transition flex items-center gap-2 disabled:opacity-70"
              >
                <span v-if="isLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                {{ isEditing ? 'Simpan Perubahan' : 'Simpan User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import api from '@/services/api';
import Swal from 'sweetalert2';

interface Role {
  role_id: number;
  role_def: string;
}

interface User {
  user_id: string;
  full_name: string;
  email: string;
  role_id: number;
  role?: Role;
}

const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const isLoading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const errors = ref<Record<string, string[]>>({});

const form = reactive({
  user_id: '',
  full_name: '',
  email: '',
  role_id: '' as number | '',
  password: ''
});

const fetchData = async () => {
  try {
    const response = await api.get('/users');
    users.value = response.data.users;
    roles.value = response.data.roles;
  } catch (error) {
    console.error('Error fetching data:', error);
    Swal.fire('Error', 'Gagal memuat data pengguna', 'error');
  }
};

onMounted(fetchData);

const openCreateModal = () => {
  isEditing.value = false;
  resetForm();
  showModal.value = true;
};

const openEditModal = (user: User) => {
  isEditing.value = true;
  form.user_id = user.user_id;
  form.full_name = user.full_name;
  form.email = user.email;
  form.role_id = user.role_id;
  form.password = ''; // Reset password field
  errors.value = {};
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
  errors.value = {};
};

const resetForm = () => {
  form.user_id = '';
  form.full_name = '';
  form.email = '';
  form.role_id = '';
  form.password = '';
};

const saveUser = async () => {
  isLoading.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await api.put(`/users/${form.user_id}`, form);
      Swal.fire('Sukses', 'Data pengguna berhasil diperbarui', 'success');
    } else {
      await api.post('/users', form);
      Swal.fire('Sukses', 'Pengguna baru berhasil ditambahkan', 'success');
    }
    await fetchData();
    closeModal();
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      Swal.fire('Error', 'Gagal menyimpan data', 'error');
    }
  } finally {
    isLoading.value = false;
  }
};

const confirmDelete = (user: User) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: `Anda akan menghapus pengguna ${user.full_name} (${user.user_id})`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/users/${user.user_id}`);
        Swal.fire('Terhapus!', 'Pengguna telah dihapus.', 'success');
        fetchData();
      } catch (error) {
        Swal.fire('Error', 'Gagal menghapus pengguna', 'error');
      }
    }
  });
};
</script>