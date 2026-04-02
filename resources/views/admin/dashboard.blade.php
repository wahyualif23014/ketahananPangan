<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-green-800 leading-tight">
            {{ __('Panel Utama Satgas Pangan - Admin') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-green-500">
                    <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Total Luas Lahan</p>
                    <h3 class="text-3xl font-black text-gray-800 mt-2">1,240 <span class="text-sm font-normal text-gray-400">Ha</span></h3>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Total Personel</p>
                    <h3 class="text-3xl font-black text-gray-800 mt-2">84 <span class="text-sm font-normal text-gray-400">Anggota</span></h3>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Laporan Masuk</p>
                    <h3 class="text-3xl font-black text-gray-800 mt-2">12 <span class="text-sm font-normal text-gray-400">Hari ini</span></h3>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-8">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Aktivitas Terkini Satgas</h4>
                    <div class="space-y-4">
                        <div class="flex items-center p-4 bg-green-50 rounded-xl border border-green-100">
                            <div class="bg-green-600 p-2 rounded-lg text-white mr-4">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-green-900">Validasi Data Lahan Selesai</p>
                                <p class="text-xs text-green-700">Polres Jember telah memperbarui data komoditas jagung.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>