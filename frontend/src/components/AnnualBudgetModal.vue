<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
        <div class="flex min-h-screen items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black bg-opacity-32 transition-opacity" @click="closeModal"></div>
          
          <!-- Modal Content -->
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#0d7d90] to-[#0a6a7a] px-6 py-4 flex items-center justify-between">
              <h2 class="text-xl font-bold text-white">Kelola Anggaran Tahunan</h2>
              <button @click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-20">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0d7d90]"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="p-6">
              <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> {{ error }}</span>
              </div>
            </div>

            <!-- Content -->
            <div v-else class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
              <!-- Action Buttons -->
              <div class="mb-6 flex justify-between items-center">
                <p class="text-gray-600">Kelola anggaran tahunan jurusan</p>
                <button 
                  @click="showCreateForm"
                  class="bg-[#0d7d90] text-white px-4 py-2 rounded-lg hover:bg-[#0a6a7a] transition-colors flex items-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Anggaran
                </button>
              </div>

              <!-- Create/Edit Form -->
              <div v-if="showForm" class="mb-6 bg-gray-50 p-6 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                  {{ editingBudget ? 'Edit Anggaran' : 'Tambah Anggaran Baru' }}
                </h3>
                
                <form @submit.prevent="submitForm" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <input 
                      v-model="formData.tahun"
                      type="text"
                      maxlength="4"
                      pattern="[0-9]{4}"
                      placeholder="2025"
                      :disabled="editingBudget !== null"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent disabled:bg-gray-100"
                      required
                    />
                    <p class="text-xs text-gray-500 mt-1">Format: 4 digit tahun (contoh: 2025)</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Anggaran (Rp)</label>
                    <input 
                      v-model.number="formData.budget"
                      type="number"
                      min="0"
                      step="1000"
                      placeholder="500000000"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0d7d90] focus:border-transparent"
                      required
                    />
                    <p class="text-xs text-gray-500 mt-1">
                      Preview: Rp {{ formatCurrency(formData.budget || 0) }}
                    </p>
                  </div>

                  <!-- Form Errors -->
                  <div v-if="formErrors" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                      <li v-for="(errors, field) in formErrors" :key="field">
                        {{ errors.join(', ') }}
                      </li>
                    </ul>
                  </div>

                  <div class="flex gap-3">
                    <button 
                      type="submit"
                      :disabled="submitting"
                      class="flex-1 bg-[#0d7d90] text-white px-4 py-2 rounded-lg hover:bg-[#0a6a7a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {{ submitting ? 'Menyimpan...' : (editingBudget ? 'Update' : 'Simpan') }}
                    </button>
                    <button 
                      type="button"
                      @click="cancelForm"
                      :disabled="submitting"
                      class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      Batal
                    </button>
                  </div>
                </form>
              </div>

              <!-- Budget List Table -->
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tahun</th>
                      <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total Anggaran</th>
                      <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Terpakai</th>
                      <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Sisa</th>
                      <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Persentase</th>
                      <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-if="budgets.length === 0">
                      <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Belum ada data anggaran tahunan
                      </td>
                    </tr>
                    <tr v-for="budget in budgets" :key="budget.budget_id" class="hover:bg-gray-50">
                      <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ budget.tahun }}</td>
                      <td class="px-4 py-3 text-sm text-right text-gray-700">
                        Rp {{ formatCurrency(budget.budget) }}
                      </td>
                      <td class="px-4 py-3 text-sm text-right text-green-600">
                        Rp {{ formatCurrency(calculateUsed(budget)) }}
                      </td>
                      <td class="px-4 py-3 text-sm text-right text-orange-600">
                        Rp {{ formatCurrency(calculateRemaining(budget)) }}
                      </td>
                      <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                          <div class="w-20 bg-gray-200 rounded-full h-2">
                            <div 
                              class="bg-green-600 h-2 rounded-full transition-all"
                              :style="{ width: `${calculatePercentage(budget)}%` }"
                            ></div>
                          </div>
                          <span class="text-xs text-gray-600">{{ calculatePercentage(budget) }}%</span>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                          <button 
                            @click="editBudget(budget)"
                            class="text-blue-600 hover:text-blue-800 transition-colors"
                            title="Edit"
                          >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>
                          <button 
                            @click="confirmDelete(budget)"
                            class="text-red-600 hover:text-red-800 transition-colors"
                            title="Hapus"
                          >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Confirmation Modal -->
    <Transition name="modal">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-[60] overflow-y-auto" @click.self="cancelDelete">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="cancelDelete"></div>
          
          <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="text-center">
              <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Anggaran?</h3>
              <p class="text-sm text-gray-500 mb-6">
                Apakah Anda yakin ingin menghapus anggaran tahun <strong>{{ budgetToDelete?.tahun }}</strong>?
                Tindakan ini tidak dapat dibatalkan.
              </p>
              <div class="flex gap-3">
                <button 
                  @click="cancelDelete"
                  :disabled="deleting"
                  class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors disabled:opacity-50"
                >
                  Batal
                </button>
                <button 
                  @click="deleteBudget"
                  :disabled="deleting"
                  class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                >
                  {{ deleting ? 'Menghapus...' : 'Hapus' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Success Toast -->
    <Transition name="toast">
      <div v-if="showToast" class="fixed bottom-4 right-4 z-[70] bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg">
        {{ toastMessage }}
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import annualBudgetService, { type AnnualBudget } from '@/services/annualBudgetService'

// Props
const props = defineProps<{
  isOpen: boolean
}>()

// Emits
const emit = defineEmits<{
  close: []
  updated: []
}>()

// State
const loading = ref(false)
const error = ref('')
const budgets = ref<AnnualBudget[]>([])
const budgetDetails = ref<Map<number, any>>(new Map()) // Store detailed budget info with usage
const showForm = ref(false)
const editingBudget = ref<AnnualBudget | null>(null)
const submitting = ref(false)
const formErrors = ref<Record<string, string[]> | null>(null)

const formData = ref({
  tahun: '',
  budget: 0
})

// Delete confirmation
const showDeleteConfirm = ref(false)
const budgetToDelete = ref<AnnualBudget | null>(null)
const deleting = ref(false)

// Toast notification
const showToast = ref(false)
const toastMessage = ref('')

// Methods
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID').format(amount || 0)
}

const calculateUsed = (budget: AnnualBudget) => {
  const details = budgetDetails.value.get(budget.budget_id)
  if (details && details.budget) {
    // Calculate used budget from the budget data
    const remaining = details.remaining_budget || 0
    return budget.budget - remaining
  }
  return 0
}

const calculateRemaining = (budget: AnnualBudget) => {
  const details = budgetDetails.value.get(budget.budget_id)
  if (details) {
    return details.remaining_budget || 0
  }
  return budget.budget
}

const calculatePercentage = (budget: AnnualBudget) => {
  const details = budgetDetails.value.get(budget.budget_id)
  if (details && details.usage_percentage !== undefined) {
    return Math.round(details.usage_percentage)
  }
  const used = calculateUsed(budget)
  if (budget.budget === 0) return 0
  return Math.round((used / budget.budget) * 100)
}

const fetchBudgets = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await annualBudgetService.getAll()
    
    if (response.success && Array.isArray(response.data)) {
      budgets.value = response.data
      
      // Fetch detailed data for each budget to get usage statistics
      for (const budget of response.data) {
        try {
          const detailResponse = await annualBudgetService.getById(budget.budget_id)
          if (detailResponse.success && detailResponse.data) {
            budgetDetails.value.set(budget.budget_id, detailResponse.data)
          }
        } catch (err) {
          console.error(`Failed to fetch details for budget ${budget.budget_id}:`, err)
        }
      }
    } else {
      error.value = response.message || 'Failed to load budgets'
    }
  } catch (err: any) {
    error.value = err.message || 'An error occurred'
  } finally {
    loading.value = false
  }
}

const showCreateForm = () => {
  editingBudget.value = null
  formData.value = {
    tahun: '',
    budget: 0
  }
  formErrors.value = null
  showForm.value = true
}

const editBudget = (budget: AnnualBudget) => {
  editingBudget.value = budget
  formData.value = {
    tahun: budget.tahun,
    budget: budget.budget
  }
  formErrors.value = null
  showForm.value = true
}

const cancelForm = () => {
  showForm.value = false
  editingBudget.value = null
  formData.value = {
    tahun: '',
    budget: 0
  }
  formErrors.value = null
}

const submitForm = async () => {
  submitting.value = true
  formErrors.value = null
  
  try {
    let response
    
    if (editingBudget.value) {
      // Update existing budget
      response = await annualBudgetService.update(editingBudget.value.budget_id, {
        budget: formData.value.budget
      })
    } else {
      // Create new budget
      response = await annualBudgetService.create(formData.value)
    }
    
    if (response.success) {
      showToastNotification(response.message || 'Anggaran berhasil disimpan')
      cancelForm()
      await fetchBudgets()
      emit('updated')
    } else {
      if (response.errors) {
        formErrors.value = response.errors
      } else {
        error.value = response.message || 'Failed to save budget'
      }
    }
  } catch (err: any) {
    error.value = err.message || 'An error occurred'
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (budget: AnnualBudget) => {
  budgetToDelete.value = budget
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  showDeleteConfirm.value = false
  budgetToDelete.value = null
}

const deleteBudget = async () => {
  if (!budgetToDelete.value) return
  
  deleting.value = true
  
  try {
    const response = await annualBudgetService.delete(budgetToDelete.value.budget_id)
    
    if (response.success) {
      showToastNotification(response.message || 'Anggaran berhasil dihapus')
      cancelDelete()
      await fetchBudgets()
      emit('updated')
    } else {
      error.value = response.message || 'Failed to delete budget'
      cancelDelete()
    }
  } catch (err: any) {
    error.value = err.message || 'An error occurred'
    cancelDelete()
  } finally {
    deleting.value = false
  }
}

const showToastNotification = (message: string) => {
  toastMessage.value = message
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

const closeModal = () => {
  if (!submitting.value && !deleting.value) {
    emit('close')
  }
}

// Watch for modal open
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    fetchBudgets()
    showForm.value = false
    editingBudget.value = null
  }
})
</script>

<style scoped>
/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}

/* Toast transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
