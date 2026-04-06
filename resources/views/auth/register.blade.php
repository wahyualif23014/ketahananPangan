<x-guest-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    <div class="min-h-screen flex items-center justify-center bg-white relative overflow-hidden font-sans">

        <div class="absolute top-[-5%] left-[-5%] w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 w-full max-w-[1000px] flex flex-col md:flex-row bg-white rounded-[2rem] shadow-[0_20px_60px_rgba(0,0,0,0.1)] overflow-hidden m-4">

            <div class="w-full md:w-[35%] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/asfalt-dark.png')] opacity-20 pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/30 rounded-full blur-[60px]"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/40">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <span class="text-white font-black tracking-tighter text-xs uppercase">Sikap <span class="text-emerald-400">Presisi</span></span>
                    </div>
                </div>

                <div class="relative z-10 my-6 flex justify-center items-center">
                    <dotlottie-player
                        src="https://lottie.host/b4532492-4f36-400f-a590-f9c38d864ab2/GFmmJleTqw.lottie"
                        background="transparent"
                        speed="1"
                        class="w-full max-w-[180px] md:max-w-[220px] animate-float drop-shadow-[0_10px_30px_rgba(16,185,129,0.5)]"
                        loop
                        autoplay>
                    </dotlottie-player>
                </div>

                <div class="relative z-10 mb-2 text-center md:text-left">
                    <h3 class="text-2xl font-bold text-white leading-tight tracking-tight italic">
                        Securing <br> <span class="text-emerald-400">Food Supply</span> <br> for Tomorrow.
                    </h3>
                </div>
            </div>

            <div class="w-full md:w-[65%] p-6 md:p-10 flex flex-col justify-center bg-white">

                <div class="mb-8 text-center">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight" style="font-family: 'Times New Roman', serif;">Tambah Data Anggota</h2>
                    <p class="text-slate-400 text-[10px] font-medium mt-1.5 uppercase tracking-wider">Satgas Pangan Command Center</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">ID Anggota (Manual)</label>
                            <input type="number" name="id_anggota" value="{{ old('id_anggota') }}" required
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/30 transition-all"
                                placeholder="Masukkan ID">
                            @error('id_anggota') <p class="text-red-500 text-[9px] mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                            <input type="text" name="nama_anggota" value="{{ old('nama_anggota') }}" required
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/30 transition-all"
                                placeholder="Nama Lengkap Sesuai KTA">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NRP / Username</label>
                            <input type="text" name="username" value="{{ old('username') }}" required
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/30 transition-all"
                                placeholder="Masukkan NRP">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Jabatan</label>
                            <select name="id_jabatan" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                <option value="" disabled selected>Pilih Jabatan</option>
                                @foreach($jabatans as $j)
                                <option value="{{ $j->id_jabatan }}" {{ old('id_jabatan') == $j->id_jabatan ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Role Akun</label>
                            <select name="role" required class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all">
                                <option value="view" {{ old('role') == 'view' ? 'selected' : '' }}>VIEW</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>ADMIN</option>
                                <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>OPERATOR</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">ID Tugas</label>
                            <input type="number" name="id_tugas" value="{{ old('id_tugas') }}"
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                placeholder="Opsional">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">No. Telepon</label>
                            <input type="text" name="no_telp_anggota" value="{{ old('no_telp_anggota') }}"
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                placeholder="08123xxxx">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Password</label>
                            <input type="password" name="password" required
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                placeholder="••••••••">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" required
                                class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 transition-all"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 bg-slate-950 hover:bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-[0.2em] transition-all duration-300 shadow-xl shadow-slate-900/20">
                            Simpan Data Anggota
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <a class="text-[10px] font-bold text-slate-400 hover:text-emerald-600 transition-colors uppercase tracking-widest" href="{{ route('login') }}">
                        Kembali ke Halaman Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'OK'
        });
    </script>
    @endif

    <style>
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</x-guest-layout>