<template>
    <!-- Main Content -->
    <div class="flex-1 p-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Detail akun</h2>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <form @submit.prevent="showConfirmation" class="space-y-6">
                        <!-- Name Field -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name Lengkap
                            </label>
                            <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your full name"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                        </div>

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                            </label>
                            <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your email"
                            />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <!-- Password Change Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Ganti Password</h3>
                            
                            <!-- Current Password -->
                            <div class="mb-4">
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password saat ini
                            </label>
                            <input
                                type="password"
                                id="current_password"
                                v-model="form.current_password"
                                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter current password"
                            />
                            <p v-if="errors.current_password" class="mt-1 text-sm text-red-600">{{ errors.current_password[0] }}</p>
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password baru
                            </label>
                            <input
                                type="password"
                                id="new_password"
                                v-model="form.new_password"
                                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter new password"
                            />
                            <p v-if="errors.new_password" class="mt-1 text-sm text-red-600">{{ errors.new_password[0] }}</p>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi password baru
                            </label>
                            <input
                                type="password"
                                id="new_password_confirmation"
                                v-model="form.new_password_confirmation"
                                class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Confirm new password"
                            />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                            type="submit"
                            :disabled="loading"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl shadow-sm text-[#F6F5F4] bg-[#0D7D90] hover:bg-[#0D7D90]/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0D7D90] disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                            <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                            Edit Data
                            </button>
                        </div>
                    </form>
                    <!-- Success/Error Messages -->
                    <div v-if="message" 
                    :class="['p-4 rounded-md mb-6', messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200']">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i :class="[messageType === 'success' ? 'fas fa-check text-green-400' : 'fas fa-exclamation-triangle text-red-400']"></i>
                            </div>
                            <div class="ml-3">
                                <p :class="['text-sm font-medium', messageType === 'success' ? 'text-green-800' : 'text-red-800']">
                                    {{ message }}
                                </p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="message = ''" 
                                            :class="['inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2', messageType === 'success' ? 'bg-green-50 text-green-500 hover:bg-green-100 focus:ring-green-600' : 'bg-red-50 text-red-500 hover:bg-red-100 focus:ring-red-600']">
                                        <i class="fas fa-times h-3 w-3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Konfirmasi Perubahan</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-2">
                    <p class="text-sm text-gray-500 mb-4">
                        Apakah Anda yakin ingin menyimpan perubahan data akun?
                    </p>
                </div>
            
                <!-- Changes Summary -->
                <div v-if="changes.length > 0" class="bg-gray-50 p-3 rounded-md">
                    <p class="text-sm font-medium text-gray-700 mb-2">Perubahan:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li v-for="change in changes" :key="change" class="flex items-start">
                            <i class="fas fa-chevron-right text-blue-500 mt-0.5 mr-2 text-xs"></i>
                            <span>{{ change }}</span>
                        </li>
                    </ul>
                </div>
                <div v-else class="bg-yellow-50 p-3 rounded-md">
                    <p class="text-sm text-yellow-700">Tidak ada perubahan yang terdeteksi</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 mt-6">
                <button
                    @click="showModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Batal
                </button>
                <button
                    @click="updateAccount"
                    :disabled="loading"
                    class="px-4 py-2 text-sm font-medium text-white bg-[#0D7D90] border border-transparent rounded-md shadow-sm hover:bg-[#0D7D90]/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0D7D90] disabled:opacity-50"
                >
                    <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AccountSettings',
  data() {
    return {
      loading: false,
      showModal: false,
      message: '',
      messageType: 'success',
      user: {},
      form: {
        name: '',
        email: '',
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      },
      errors: {},
      originalData: {}
    }
  },
  computed: {
    changes() {
      const changes = []
      
      // Check name change
      if (this.form.name !== this.originalData.name) {
        changes.push(`Nama: "${this.originalData.name}" → "${this.form.name}"`)
      }
      
      // Check email change
      if (this.form.email !== this.originalData.email) {
        changes.push(`Email: "${this.originalData.email}" → "${this.form.email}"`)
      }
      
      // Check password change
      if (this.form.new_password) {
        changes.push('Password: Akan diubah')
      }
      
      return changes
    }
  },
  async mounted() {
    await this.fetchUserData()
  },
  methods: {
    async fetchUserData() {
      try {
        const token = localStorage.getItem('token')
        const API_URL = import.meta.env.VITE_API_BASE_URL
        
        const response = await axios.get(`${API_URL}/user`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
        
        this.user = response.data
        this.form.name = this.user.full_name || this.user.name
        this.form.email = this.user.email
        
        // Store original data for comparison
        this.originalData = {
          name: this.user.full_name || this.user.name,
          email: this.user.email
        }
      } catch (error) {
        console.error('Error fetching user data:', error)
        this.showMessage('Gagal memuat data pengguna', 'error')
      }
    },

    showConfirmation() {
      // Basic validation
      if (this.form.new_password && !this.form.current_password) {
        this.showMessage('Password saat ini diperlukan untuk mengubah password', 'error')
        return
      }

      if (this.form.new_password && this.form.new_password !== this.form.new_password_confirmation) {
        this.showMessage('Konfirmasi password tidak cocok', 'error')
        return
      }

      this.showModal = true
    },

    async updateAccount() {
      this.loading = true
      this.errors = {}

      try {
        // Prepare data - only send fields that have values
        const updateData = {}
        
        if (this.form.name !== this.originalData.name) {
          updateData.name = this.form.name
        }
        
        if (this.form.email !== this.originalData.email) {
          updateData.email = this.form.email
        }
        
        if (this.form.new_password) {
          updateData.current_password = this.form.current_password
          updateData.new_password = this.form.new_password
          updateData.new_password_confirmation = this.form.new_password_confirmation
        }

        // If no changes, close modal and return
        if (Object.keys(updateData).length === 0) {
          this.showModal = false
          this.showMessage('Tidak ada perubahan yang dilakukan', 'info')
          this.loading = false
          return
        }

        const token = localStorage.getItem('token')
        const API_URL = import.meta.env.VITE_API_BASE_URL

        const response = await axios.put(`${API_URL}/account`, updateData, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })

        this.showModal = false
        this.showMessage('Data akun berhasil diperbarui', 'success')
        
        // Update original data
        this.originalData.name = this.form.name
        this.originalData.email = this.form.email
        
        // Clear password fields
        this.form.current_password = ''
        this.form.new_password = ''
        this.form.new_password_confirmation = ''

      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors
          this.showMessage('Terdapat kesalahan dalam pengisian form', 'error')
        } else {
          this.showMessage('Gagal memperbarui data akun', 'error')
        }
      } finally {
        this.loading = false
      }
    },

    showMessage(text, type) {
      this.message = text
      this.messageType = type
      
      // Auto hide success messages after 5 seconds
      if (type === 'success') {
        setTimeout(() => {
          this.message = ''
        }, 5000)
      }
    }
  }
}
</script>
