@extends('layouts.app')

@section('header', 'Dashboard Utama')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Anggota</p>
            <h3 class="text-2xl font-bold text-gray-800">1,284</h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.416a2 2 0 011.082-1.789l5.447-2.724a2 2 0 011.836 0l5.447 2.724A2 2 0 0118 7.416v7.966a2 2 0 01-1.082 1.79l-5.447 2.723a2 2 0 01-1.836 0z"></path></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Luas Lahan (Ha)</p>
            <h3 class="text-2xl font-bold text-gray-800">450.5</h3>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-700">Aktivitas Terbaru</h3>
        @can('manage anggota')
            <button class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800 transition">Tambah Anggota</button>
        @endcan
    </div>
    <div class="p-6">
        </div>
</div>
@endsection