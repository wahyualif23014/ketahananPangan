@extends('layouts.app')
@section('title', 'Profil Pengguna')

@section('content')
<div class="space-y-6 animate-fade-in-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Profil Pengguna</h2>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Informasi Akun dan Keamanan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Profil Data Diri (Read-Only) --}}
        <div class="xl:col-span-1">
            <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 p-6 md:p-8 relative overflow-hidden h-full flex flex-col">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-50 rounded-full opacity-60 pointer-events-none"></div>
                
                <div class="flex flex-col items-center justify-center text-center mb-8 relative z-10 flex-shrink-0">
                    <div class="w-24 h-24 rounded-[1.5rem] bg-gradient-to-tr from-emerald-500 to-emerald-400 text-white flex items-center justify-center font-bold text-4xl shadow-xl shadow-emerald-500/30 mb-5 border-4 border-white transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        {{ collect(explode(' ', $user->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                    </div>
                    <h3 class="text-xl font-black text-slate-800">{{ $user->nama_anggota }}</h3>
                    <p class="text-[11px] font-black text-emerald-600 uppercase tracking-widest mt-1.5 px-3 py-1 bg-emerald-50 rounded-lg inline-block">{{ $jabatan }}</p>
                </div>

                <div class="space-y-5 relative z-10 flex-1">
                    <div class="group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Username</p>
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 group-hover:border-emerald-200 group-hover:bg-emerald-50/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <p class="text-sm font-bold text-slate-700">{{ $user->username }}</p>
                        </div>
                    </div>
                    
                    <div class="group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nomor Telepon</p>
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 group-hover:border-emerald-200 group-hover:bg-emerald-50/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <p class="text-sm font-bold text-slate-700">{{ $user->no_telp_anggota ?? 'Tidak Tersedia' }}</p>
                        </div>
                    </div>
                    
                    <div class="group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Wilayah Tugas</p>
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 group-hover:border-emerald-200 group-hover:bg-emerald-50/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="text-sm font-bold text-slate-700">{{ $wilayah }}</p>
                        </div>
                    </div>

                    <div class="group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Hak Akses (Role)</p>
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 group-hover:border-emerald-200 group-hover:bg-emerald-50/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="inline-block px-2.5 py-1 {{ $user->role === 'admin' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : ($user->role === 'operator' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-blue-100 text-blue-700 border-blue-200') }} text-[10px] font-black uppercase tracking-widest rounded shadow-sm border">
                                {{ $user->role ?? 'Administrator' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Ubah Password --}}
        <div class="xl:col-span-2">
            <div class="bg-white rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 p-6 md:p-8 md:px-10 relative overflow-hidden h-full flex flex-col">
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-indigo-50 rounded-full opacity-60 pointer-events-none"></div>
                
                <div class="relative z-10 flex-1 flex flex-col">
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5 flex-shrink-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 transform -rotate-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800">Ubah Kata Sandi</h3>
                            <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mt-1">Pastikan akun Anda menggunakan kata sandi yang aman</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('password.update') }}" class="flex-1 flex flex-col">
                        @csrf
                        @method('put')
                        
                        <div class="space-y-6 max-w-xl flex-1">
                            <div class="space-y-1" x-data="{ show: false }">
                                <label for="update_password_current_password" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest ml-1">Kata Sandi Saat Ini</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </div>
                                    <input id="update_password_current_password" name="current_password" :type="show ? 'text' : 'password'" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium rounded-xl pl-11 pr-12 py-3.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all outline-none" autocomplete="current-password" placeholder="Masukkan kata sandi saat ini">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                                @if($errors->updatePassword->get('current_password'))
                                    <p class="text-[11px] font-bold text-rose-500 mt-1.5 ml-1">{{ $errors->updatePassword->first('current_password') }}</p>
                                @endif
                            </div>

                            <div class="space-y-1" x-data="{ show: false }">
                                <label for="update_password_password" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest ml-1">Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <input id="update_password_password" name="password" :type="show ? 'text' : 'password'" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium rounded-xl pl-11 pr-12 py-3.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all outline-none" autocomplete="new-password" placeholder="Masukkan kata sandi baru">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                                @if($errors->updatePassword->get('password'))
                                    <p class="text-[11px] font-bold text-rose-500 mt-1.5 ml-1">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div class="space-y-1" x-data="{ show: false }">
                                <label for="update_password_password_confirmation" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest ml-1">Konfirmasi Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    </div>
                                    <input id="update_password_password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-medium rounded-xl pl-11 pr-12 py-3.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all outline-none" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-500 focus:outline-none transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                                @if($errors->updatePassword->get('password_confirmation'))
                                    <p class="text-[11px] font-bold text-rose-500 mt-1.5 ml-1">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-5 pt-6 mt-6 border-t border-slate-100 flex-shrink-0">
                            <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-[11px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-indigo-500/30 active:scale-95 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Perubahan
                            </button>

                            @if (session('status') === 'password-updated')
                                <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms x-init="setTimeout(() => show = false, 4000)" class="flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-lg border border-emerald-100">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest">Berhasil Diperbarui</p>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
