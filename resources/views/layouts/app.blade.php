<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIKAP PRESISI - POLDA JAWA TIMUR</title>
    <link rel="icon" type="image/png" href="{{ asset('logo-sikap.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logo-sikap.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ramabhadra&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- NProgress for smooth page transitions -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <script>
        // Start progress bar as soon as the head parses
        NProgress.configure({ showSpinner: false, minimum: 0.1, speed: 400 });
        NProgress.start();
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ================================================================
           ENTERPRISE TOAST NOTIFICATION SYSTEM
        ================================================================ */
        @keyframes toast-slide-in {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.92);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toast-slide-out {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(16px) scale(0.92);
            }
        }

        @keyframes toast-progress {
            from { width: 100%; }
            to   { width: 0%; }
        }

        @keyframes confirm-scale-in {
            from {
                opacity: 0;
                transform: scale(0.88) translateY(24px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .toast-enter {
            animation: toast-slide-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .toast-leave {
            animation: toast-slide-out 0.3s ease-in forwards;
        }

        .toast-progress-bar {
            animation: toast-progress linear forwards;
        }

        .confirm-pop-in {
            animation: confirm-scale-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .font-tactical {
            font-family: 'Ramabhadra', 'Figtree', sans-serif;
        }

        @layer utilities {
            .custom-scrollbar::-webkit-scrollbar {
                width: 5px;
                height: 5px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #1F2937;
                border-radius: 99px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #374151;
            }
        }

        /* NProgress custom color */
        #nprogress .bar {
            background: #10b981 !important; /* emerald-500 */
            height: 3px !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px #10b981, 0 0 5px #10b981 !important;
        }
        #nprogress .spinner-icon {
            border-top-color: #10b981 !important;
            border-left-color: #10b981 !important;
        }

        /* Smooth Page Transition */
        .page-transition-enter {
            animation: fadeInPage 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .page-transition-leave {
            opacity: 0.5;
            transform: scale(0.995);
            pointer-events: none;
            transition: all 0.3s ease-in-out;
        }
        @keyframes fadeInPage {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#f3f4f6] text-slate-900 overflow-hidden"
    x-data="{ sidebarExpanded: window.innerWidth >= 768 }"
    @resize.window="sidebarExpanded = window.innerWidth >= 768">

    <div class="flex h-screen overflow-hidden">

        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-300">

            <header class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 shadow-sm relative">
                <div class="flex items-center gap-3">
                    <button @click="sidebarExpanded = !sidebarExpanded"
                        class="p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <svg x-show="!sidebarExpanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="sidebarExpanded" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>

                    <h1 class="text-sm font-semibold text-slate-800 tracking-tight sm:text-base">SISTEM KETAHANAN PANGAN</h1>
                </div>

                <div class="flex items-center gap-4" x-data="{ userOpen: false }">
                    <div class="text-right hidden sm:block">
                        <p class="text-[13px] font-semibold text-slate-800 leading-tight">
                            {{ Auth::user()->nama_anggota }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-500 tracking-wide mt-0.5 capitalize">
                            {{ Auth::user()->role ?? 'Administrator' }}
                        </p>
                    </div>
                    <div class="relative">
                        <button @click="userOpen = !userOpen"
                            class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-500 to-emerald-400 text-white flex items-center justify-center font-bold text-sm shadow-sm hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                        </button>

                        <div x-show="userOpen" x-cloak @click.outside="userOpen = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 origin-top-right">

                            @php
                            $dashboardRoute = match (Auth::user()->role) {
                            'admin' => 'admin.dashboard',
                            'operator' => 'operator.dashboard',
                            'view' => 'view.dashboard',
                            default => 'login',
                            };
                            @endphp
                            <a href="{{ route($dashboardRoute) }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Beranda
                            </a>

                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profil Pengguna
                            </a>

                            <div class="h-[1px] bg-slate-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 custom-scrollbar page-transition-enter">
                <div class="max-w-[1600px] mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- global notif -->
    <div id="global-notification-root"
        x-data="globalNotifications()"
        @notify.window="addToast($event.detail)"
        @confirm-dialog.window="openConfirm($event.detail)"
        class="pointer-events-none">

        {{-- ================================================================
             ENTERPRISE TOAST NOTIFICATION — Bottom Center, Premium Style
        ================================================================ --}}
        <div class="fixed inset-0 z-[9999] flex flex-col items-center justify-center p-4 gap-4 pointer-events-none" x-show="toasts.length > 0">
            
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity pointer-events-auto"
                 x-show="toasts.length > 0"
                 x-transition.opacity></div>

            <template x-for="toast in toasts" :key="toast.id">
                <div :id="'toast-' + toast.id"
                    class="toast-enter pointer-events-auto relative flex flex-col items-center text-center p-8 rounded-[2rem] bg-white w-full max-w-sm overflow-hidden transform transition-all duration-300"
                    style="box-shadow: 0 32px 80px -16px rgba(0,0,0,0.3), 0 0 0 1px rgba(0,0,0,0.05);"
                    :class="{
                        'border-t-4 border-emerald-500': toast.type === 'success',
                        'border-t-4 border-rose-500':    toast.type === 'error',
                        'border-t-4 border-amber-500':   toast.type === 'warning',
                        'border-t-4 border-sky-500':     toast.type === 'info',
                    }">
                    
                    {{-- Big Icon with Underglow --}}
                    <div class="relative w-24 h-24 mb-6 flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full opacity-30 blur-2xl"
                            :class="{
                                'bg-emerald-500': toast.type === 'success',
                                'bg-rose-500':    toast.type === 'error',
                                'bg-amber-500':   toast.type === 'warning',
                                'bg-sky-500':     toast.type === 'info',
                            }"></div>
                        <div class="relative z-10 w-20 h-20 rounded-full flex items-center justify-center text-white shadow-2xl"
                            :class="{
                                'bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-emerald-500/40': toast.type === 'success',
                                'bg-gradient-to-br from-rose-400 to-rose-600 shadow-rose-500/40': toast.type === 'error',
                                'bg-gradient-to-br from-amber-400 to-amber-600 shadow-amber-500/40': toast.type === 'warning',
                                'bg-gradient-to-br from-sky-400 to-sky-600 shadow-sky-500/40': toast.type === 'info',
                            }">
                            <template x-if="toast.type === 'success'">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="toast.type === 'error'">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </template>
                            <template x-if="toast.type === 'warning'">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </template>
                            <template x-if="toast.type === 'info'">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                        </div>
                    </div>

                    {{-- Text Content --}}
                    <h3 class="text-xl font-black text-slate-800 tracking-tight mb-2 uppercase" x-text="toast.title"></h3>
                    <p class="text-[13px] font-bold text-slate-500 leading-relaxed px-2" x-text="toast.message"></p>

                    {{-- OK Button --}}
                    <button @click="removeToast(toast.id)"
                        class="mt-8 w-full py-4 rounded-xl text-[12px] font-black uppercase tracking-widest text-white shadow-lg transition-all active:scale-95"
                        :class="{
                            'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/30': toast.type === 'success',
                            'bg-rose-500 hover:bg-rose-600 shadow-rose-500/30': toast.type === 'error',
                            'bg-amber-500 hover:bg-amber-600 shadow-amber-500/30': toast.type === 'warning',
                            'bg-sky-500 hover:bg-sky-600 shadow-sky-500/30': toast.type === 'info',
                        }">
                        Tutup
                    </button>

                    {{-- Auto-dismiss Progress Bar --}}
                    <div class="absolute bottom-0 left-0 right-0 h-[5px] bg-slate-100">
                        <div class="h-full toast-progress-bar"
                            :style="'animation-duration: ' + toast.duration + 'ms'"
                            :class="{
                                'bg-emerald-500': toast.type === 'success',
                                'bg-rose-500':    toast.type === 'error',
                                'bg-amber-500':   toast.type === 'warning',
                                'bg-sky-500':     toast.type === 'info',
                            }"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- ================================================================
             ENTERPRISE CONFIRM DIALOG — Center Screen, Premium Style
        ================================================================ --}}
        <div x-show="confirm.open"
            x-cloak
            class="fixed inset-0 z-[10000] flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div x-show="confirm.open"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="resolveConfirm(false)"
                class="fixed inset-0 pointer-events-auto"
                style="background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"></div>

            {{-- Dialog Box —— center screen, max-w-sm, enterprise card --}}
            <div x-show="confirm.open"
                x-transition:enter="ease-out duration-280"
                x-transition:enter-start="opacity-0 scale-90 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-180"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-92 translate-y-4"
                class="relative z-10 w-full max-w-sm bg-white rounded-[20px] overflow-hidden pointer-events-auto"
                style="box-shadow: 0 24px 64px -12px rgba(0,0,0,0.28), 0 0 0 1px rgba(0,0,0,0.04);">

                {{-- Top Gradient Bar --}}
                <div class="h-1 w-full"
                    :class="{
                        'bg-gradient-to-r from-rose-400  to-rose-600':    confirm.type === 'danger',
                        'bg-gradient-to-r from-amber-400 to-orange-500':  confirm.type === 'warning',
                        'bg-gradient-to-r from-sky-400   to-blue-500':    confirm.type === 'info',
                        'bg-gradient-to-r from-emerald-400 to-teal-500':  confirm.type === 'success',
                    }"></div>

                <div class="px-7 pt-7 pb-6">

                    {{-- Icon Badge --}}
                    <div class="flex justify-center mb-5">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center"
                            :class="{
                                'bg-rose-50':    confirm.type === 'danger',
                                'bg-amber-50':   confirm.type === 'warning',
                                'bg-sky-50':     confirm.type === 'info',
                                'bg-emerald-50': confirm.type === 'success',
                            }">
                            <template x-if="confirm.type === 'danger'">
                                <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'warning'">
                                <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'info'">
                                <svg class="w-8 h-8 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'success'">
                                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </template>
                        </div>
                    </div>

                    {{-- Text --}}
                    <div class="text-center mb-6">
                        <h3 class="text-base font-bold text-slate-900 mb-1.5" x-text="confirm.title"></h3>
                        <p class="text-[13px] text-slate-500 leading-relaxed" x-text="confirm.message"></p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-2.5">
                        <button @click="resolveConfirm(false)"
                            class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-[12px] rounded-xl transition-all active:scale-95"
                            x-text="confirm.cancelText"></button>
                        <button @click="resolveConfirm(true)"
                            class="flex-1 py-2.5 px-4 font-semibold text-[12px] text-white rounded-xl transition-all active:scale-95"
                            :class="{
                                'bg-rose-500 hover:bg-rose-600':       confirm.type === 'danger',
                                'bg-amber-500 hover:bg-amber-600':     confirm.type === 'warning',
                                'bg-sky-500 hover:bg-sky-600':         confirm.type === 'info',
                                'bg-emerald-500 hover:bg-emerald-600': confirm.type === 'success',
                            }"
                            x-text="confirm.confirmText"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Flash Toast Auto-trigger --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'success',
                title: 'Berhasil!',
                message: @json(session('success')),
                duration: 5000
            }
        })));
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'error',
                title: 'Terjadi Kesalahan',
                message: @json(session('error')),
                duration: 7000
            }
        })));
    </script>
    @endif
    @if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'warning',
                title: 'Perhatian',
                message: @json(session('warning')),
                duration: 6000
            }
        })));
    </script>
    @endif
    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'info',
                title: 'Informasi',
                message: @json(session('info')),
                duration: 5000
            }
        })));
    </script>
    @endif
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('notify', {
            detail: {
                type: 'error',
                title: 'Validasi Gagal',
                message: @json($errors->first()),
                duration: 7000
            }
        })));
    </script>
    @endif

    <script>
        function globalNotifications() {
            return {
                toasts: [],
                confirm: {
                    open: false,
                    type: 'danger',
                    title: '',
                    message: '',
                    confirmText: 'Ya, Lanjutkan',
                    cancelText: 'Batal',
                    resolve: null,
                },

                addToast({
                    type = 'info',
                    title = '',
                    message = '',
                    duration = 5000
                }) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({
                        id,
                        type,
                        title,
                        message,
                        duration
                    });
                    setTimeout(() => this.removeToast(id), duration);
                },

                removeToast(id) {
                    const el = document.getElementById('toast-' + id);
                    if (el) {
                        el.classList.remove('toast-enter');
                        el.classList.add('toast-leave');
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 350);
                    } else {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }
                },

                openConfirm({
                    type = 'danger',
                    title = 'Konfirmasi',
                    message = '',
                    confirmText = 'Ya, Lanjutkan',
                    cancelText = 'Batal',
                    resolve
                }) {
                    this.confirm = {
                        open: true,
                        type,
                        title,
                        message,
                        confirmText,
                        cancelText,
                        resolve
                    };
                },

                resolveConfirm(result) {
                    this.confirm.open = false;
                    if (typeof this.confirm.resolve === 'function') {
                        setTimeout(() => this.confirm.resolve(result), 200);
                    }
                },
            };
        }

        function $notify(type, title, message = '', duration = 5000) {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    type,
                    title,
                    message,
                    duration
                }
            }));
        }

        function $confirm({
            type = 'danger',
            title = 'Konfirmasi Tindakan',
            message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            confirmText = 'Ya, Lanjutkan',
            cancelText = 'Batal'
        } = {}) {
            return new Promise((resolve) => {
                window.dispatchEvent(new CustomEvent('confirm-dialog', {
                    detail: {
                        type,
                        title,
                        message,
                        confirmText,
                        cancelText,
                        resolve
                    }
                }));
            });
        }


        function handleFormConfirm(event, title, message, type = 'danger') {
            event.preventDefault();
            const form = event.target;
            $confirm({
                type,
                title,
                message,
                confirmText: type === 'danger' ? 'Ya, Hapus' : 'Ya, Proses',
                cancelText: 'Batal'
            }).then(ok => {
                if (ok) form.submit();
            });
            return false;
        }
    </script>

    <!-- Global Page Loader & Transitions Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            NProgress.done();
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') || link.getAttribute('target') === '_blank') return;
            
            if (link.hasAttribute('download') || link.origin !== window.location.origin) return;

            if (link.pathname === window.location.pathname && link.search === window.location.search) return;

            NProgress.start();
            document.getElementById('main-content')?.classList.add('page-transition-leave');
        });

        document.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return;
            NProgress.start();
            document.getElementById('main-content')?.classList.add('page-transition-leave');
        });

        window.addEventListener('pageshow', function(e) {
            NProgress.done();
            document.getElementById('main-content')?.classList.remove('page-transition-leave');
        });
    </script>
    
    @include('components.floating-chat')
</body>

</html>