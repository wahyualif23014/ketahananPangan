<x-guest-layout>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    <div class="min-h-screen flex items-center justify-center bg-white relative overflow-hidden font-sans">

        <div class="absolute top-[-5%] left-[-5%] w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 w-full max-w-[720px] flex flex-col md:flex-row bg-white rounded-[2rem] shadow-[0_20px_60px_rgba(0,0,0,0.1)] overflow-hidden m-4 border border-slate-200">

            <div class="w-full md:w-[45%] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 flex flex-col justify-between relative overflow-hidden border-b md:border-b-0 md:border-r border-slate-800">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/asfalt-dark.png')] opacity-20 pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/30 rounded-full blur-[60px]"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-emerald-500 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/40">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <span class="text-white font-black tracking-tighter text-xs uppercase">Sikap <span class="text-emerald-400">Presisi</span></span>
                    </div>
                </div>

                {{-- AREA ANIMASI LOTTIE (Tema Pertanian Modern) --}}
                <div class="relative z-10 my-6 flex justify-center items-center">
                    {{--
                        Link JSON Lottie Pertanian (Valid):
                        https://lottie.host/748805f6-7b89-498c-9c7b-7b068e16e6d1/C0vL6r5p2S.json
                    --}}
                    <dotlottie-player
                        src="https://lottie.host/b4532492-4f36-400f-a590-f9c38d864ab2/GFmmJleTqw.lottie"
                        background="transparent"
                        speed="1"
                        class="w-full max-w-[200px] md:max-w-[250px] animate-float drop-shadow-[0_10px_30px_rgba(16,185,129,0.5)]"
                        loop
                        autoplay>
                    </dotlottie-player>
                </div>

                <div class="relative z-10 mb-2 text-center md:text-left">
                    <h3 class="text-2xl font-bold text-white leading-tight tracking-tight italic">
                        Securing <br> <span class="text-emerald-400">Food Supply</span> <br> for Tomorrow.
                    </h3>
                    <p class="mt-3 text-slate-400 text-[9px] leading-relaxed uppercase tracking-[0.2em] font-medium opacity-80">
                        <br> Polda Jawa Timur
                    </p>
                </div>
            </div>

            <div class="w-full md:w-[55%] p-6 md:p-10 flex flex-col justify-center bg-white">

                <div class="mb-6 text-center">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight" style="font-family: 'Times New Roman', serif;">Welcome</h2>
                    <p class="text-slate-400 text-[10px] font-medium mt-1.5 uppercase tracking-wider">Ketahanan Pangan</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1">
                        <label for="username" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">NRP (Nomor Registrasi Pokok)</label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/30 focus:bg-white transition-all placeholder:text-slate-300"
                            placeholder="Masukkan NRP Anda">
                        @if ($errors->has('username'))
                        <span class="text-red-500 text-[10px] mt-1 ml-1">{{ $errors->first('username') }}</span>
                        @endif
                    </div>

                    <div class="space-y-1" x-data="{ show: false }">
                        <div class="flex justify-between items-center px-1">
                            <label for="password" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Password</label>
                            @if (Route::has('password.request'))
                            <a class="text-[9px] font-bold text-emerald-600 hover:text-emerald-700 transition-colors" href="{{ route('password.request') }}">Lupa?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required
                                class="block w-full px-4 py-2.5 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500/30 focus:bg-white transition-all placeholder:text-slate-300"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-show="show" x-cloak style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.978 9.978 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-[9px]" />
                    </div>

                    <div class="flex items-center justify-between pb-1">
                        <label class="flex items-center">
                            <input id="remember_me" type="checkbox" name="remember" class="w-3.5 h-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20">
                            <span class="ms-2 text-[10px] font-bold text-slate-400">Ingat saya</span>
                        </label>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full py-3 bg-slate-950 hover:bg-emerald-600 text-white rounded-xl text-[11px] font-black uppercase tracking-[0.2em] shadow-lg shadow-slate-950/10 hover:shadow-emerald-500/30 transition-all transform active:scale-[0.98]">
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">v1.0 © 2026 Polda Jatim</p>
                </div>
            </div>
        </div>
    </div>

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