<template>
    <div class="flex justify-center items-center min-h-screen">
        <form @submit.prevent="submitForm" class="bg-[#0d7d90] text-[#F6F5F4] rounded-3xl w-full max-w-4xl p-8">
            <h1 class="text-2xl font-bold text-center mb-8">Pengajuan Dokumen TOR</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div>
                        <label for="namaKegiatan" class="block mb-2">Nama Kegiatan</label>
                        <input type="text" id="namaKegiatan" v-model="form.namaKegiatan" 
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32">
                    </div>

                    <div>
                        <label for="tujuan" class="block mb-2">Tujuan</label>
                        <textarea id="tujuan" v-model="form.tujuan" rows="3"
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32"></textarea>
                    </div>

                    <div>
                        <label for="latarBelakang" class="block mb-2">Latar Belakang</label>
                        <textarea id="latarBelakang" v-model="form.latarBelakang" rows="4"
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32"></textarea>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="jadwal" class="block mb-2">Jadwal</label>
                        <input type="date" id="jadwal" v-model="form.jadwal"
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32">
                    </div>

                    <div>
                        <label for="anggaran" class="block mb-2">Anggaran</label>
                        <div class="flex items-center">
                            <span class="bg-[#f6f5f4] text-[#0d7d90] p-3 rounded-l-xl">Rp</span>
                            <input type="number" id="anggaran" v-model="form.anggaran"
                                class="w-full -ml-3 p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-r-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32">
                        </div>
                    </div>

                    <div>
                        <label for="pic" class="block mb-2">PIC</label>
                        <input type="text" id="pic" v-model="form.pic"
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32">
                    </div>

                    <div>
                        <label for="peserta" class="block mb-2">Peserta</label>
                        <textarea id="peserta" v-model="form.peserta" rows="2"
                            class="w-full p-3 bg-[#f6f5f4] text-[#0d7d90] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#F6F5F4]/32"></textarea>
                    </div>
                </div>
            </div>

            <!-- Dokumen Pendukung -->
            <div class="mt-6">
                <label class="block mb-2">Dokumen Pendukung</label>
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-[#f6f5f4] border-dashed rounded-xl cursor-pointer bg-[#0d7d90] hover:bg-[#0a6a7a] transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-4 text-[#f6f5f4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="mb-2 text-sm text-[#f6f5f4]">
                                <span class="font-semibold">Click to upload</span>
                            </p>
                        </div>
                        <input type="file" @change="handleFileUpload" class="hidden" accept=".pdf,.doc,.docx"/>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-center">
                <button type="submit" 
                    class="bg-[#f6f5f4] text-[#0d7d90] px-8 py-3 rounded-xl hover:bg-gray-300 hover:ring-2 hover:ring-[#F6F5F4]/32 transition-colors font-semibold">
                    Submit
                </button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    data() {
        return {
            form: {
                namaKegiatan: '',
                tujuan: '',
                pic: '',
                latarBelakang: '',
                jadwal: '',
                anggaran: '',
                dokumenPendukung: null,
                peserta: ''
            }
        }
    },
    methods: {
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.form.dokumenPendukung = file;
            }
        },
        submitForm() {
            console.log('Form submitted:', this.form);
        }
    }
}
</script>