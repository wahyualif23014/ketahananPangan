<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-yellow-700 leading-tight">
            {{ __('Panel Kerja Operator Lapangan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl flex flex-col md:flex-row">
                <div class="p-8 md:w-2/3">
                    <h3 class="text-2xl font-bold text-gray-800">Halo, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-500 mt-2">Selamat bertugas kembali. Pastikan data laporan harian telah diinput sebelum pukul 16:00 WIB.</p>
                    
                    <div class="mt-8 flex gap-4">
                        <a href="#" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold transition">
                            + Input Laporan Baru
                        </a>
                        <a href="#" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold transition">
                            Lihat Riwayat
                        </a>
                    </div>
                </div>
                
                <div class="bg-yellow-50 p-8 md:w-1/3 border-l border-yellow-100">
                    <h4 class="font-bold text-yellow-800 uppercase text-xs tracking-widest mb-4">Status Wilayah Anda</h4>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-yellow-700">Kondisi Tanaman</span>
                        <span class="px-2 py-1 bg-green-200 text-green-800 text-[10px] font-bold rounded">OPTIMAL</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-yellow-700">Progress Panen</span>
                        <span class="font-bold text-yellow-900 text-lg">65%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>