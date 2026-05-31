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

        @keyframes toast-slide-in {
            from {
                opacity: 0;
                transform: translateX(100%) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes toast-slide-out {
            from {
                opacity: 1;
                transform: translateX(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateX(100%) scale(0.9);
            }
        }

        @keyframes toast-progress {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @keyframes confirm-pop-in {
            from {
                opacity: 0;
                transform: scale(0.85) translateY(16px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .toast-enter {
            animation: toast-slide-in 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .toast-leave {
            animation: toast-slide-out 0.35s ease-in forwards;
        }

        .toast-progress-bar {
            animation: toast-progress linear forwards;
        }

        .confirm-pop-in {
            animation: confirm-pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
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

        {{-- TOAST CONTAINER --}}
        <div class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none" style="max-width: 400px; width: calc(100vw - 40px);">
            <template x-for="toast in toasts" :key="toast.id">
                <div :id="'toast-' + toast.id"
                    class="toast-enter pointer-events-auto relative flex items-start gap-4 p-4 pr-5 rounded-2xl shadow-[0_20px_60px_-10px_rgba(0,0,0,0.25)] border overflow-hidden"
                    :class="{
                         'bg-white border-emerald-100': toast.type === 'success',
                         'bg-white border-rose-100': toast.type === 'error',
                         'bg-white border-amber-100': toast.type === 'warning',
                         'bg-white border-sky-100': toast.type === 'info',
                     }">

                    {{-- Accent Bar --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl"
                        :class="{
                             'bg-emerald-500': toast.type === 'success',
                             'bg-rose-500': toast.type === 'error',
                             'bg-amber-400': toast.type === 'warning',
                             'bg-sky-500': toast.type === 'info',
                         }"></div>

                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center mt-0.5"
                        :class="{
                             'bg-emerald-50 text-emerald-500': toast.type === 'success',
                             'bg-rose-50 text-rose-500': toast.type === 'error',
                             'bg-amber-50 text-amber-500': toast.type === 'warning',
                             'bg-sky-50 text-sky-500': toast.type === 'info',
                         }">
                        <template x-if="toast.type === 'success'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-[13px] font-black text-slate-800 uppercase tracking-wide leading-tight" x-text="toast.title"></p>
                        <p x-show="toast.message" class="text-[12px] font-medium text-slate-500 mt-1 leading-relaxed" x-text="toast.message"></p>
                    </div>

                    {{-- Close Button --}}
                    <button @click="removeToast(toast.id)"
                        class="flex-shrink-0 w-7 h-7 rounded-xl flex items-center justify-center text-slate-300 hover:text-slate-600 hover:bg-slate-100 transition-all duration-300 active:scale-90 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Progress Bar --}}
                    <div class="absolute bottom-0 left-1 right-0 h-[3px] rounded-b-2xl overflow-hidden">
                        <div class="h-full toast-progress-bar rounded-full"
                            :style="'animation-duration: ' + toast.duration + 'ms'"
                            :class="{
                                 'bg-emerald-400': toast.type === 'success',
                                 'bg-rose-400': toast.type === 'error',
                                 'bg-amber-400': toast.type === 'warning',
                                 'bg-sky-400': toast.type === 'info',
                             }"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- CUSTOM CONFIRM DIALOG --}}
        <div x-show="confirm.open"
            x-cloak
            class="fixed inset-0 z-[10000] flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div x-show="confirm.open"
                x-transition:enter="ease-out duration-400"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="resolveConfirm(false)"
                class="fixed inset-0 bg-slate-900/40 pointer-events-auto"></div>

            {{-- Dialog Box --}}
            <div x-show="confirm.open"
                x-transition:enter="ease-out duration-400"
                x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                class="relative z-10 w-full max-w-md bg-white rounded-[2.5rem] shadow-[0_32px_80px_-16px_rgba(0,0,0,0.3)] overflow-hidden border border-slate-100 pointer-events-auto">

                {{-- Top Accent --}}
                <div class="h-1.5 w-full"
                    :class="{
                         'bg-gradient-to-r from-rose-400 to-rose-600': confirm.type === 'danger',
                         'bg-gradient-to-r from-amber-400 to-orange-500': confirm.type === 'warning',
                         'bg-gradient-to-r from-sky-400 to-blue-500': confirm.type === 'info',
                         'bg-gradient-to-r from-emerald-400 to-teal-500': confirm.type === 'success',
                     }"></div>

                <div class="p-8">
                    {{-- Icon Area --}}
                    <div class="flex justify-center mb-6">
                        <div class="w-20 h-20 rounded-[1.5rem] flex items-center justify-center shadow-inner"
                            :class="{
                                 'bg-rose-50': confirm.type === 'danger',
                                 'bg-amber-50': confirm.type === 'warning',
                                 'bg-sky-50': confirm.type === 'info',
                                 'bg-emerald-50': confirm.type === 'success',
                             }">
                            <template x-if="confirm.type === 'danger'">
                                <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'warning'">
                                <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'info'">
                                <svg class="w-10 h-10 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="confirm.type === 'success'">
                                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>
                    </div>

                    {{-- Text --}}
                    <div class="text-center mb-8">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2" x-text="confirm.title"></h3>
                        <p class="text-[13px] font-medium text-slate-500 leading-relaxed" x-text="confirm.message"></p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3">
                        <button @click="resolveConfirm(false)"
                            class="flex-1 py-3.5 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-[11px] uppercase tracking-[0.15em] rounded-2xl transition-all duration-300 active:scale-95"
                            x-text="confirm.cancelText"></button>
                        <button @click="resolveConfirm(true)"
                            class="flex-1 py-3.5 px-6 font-black text-[11px] uppercase tracking-[0.15em] rounded-2xl transition-all duration-300 active:scale-95 shadow-lg"
                            :class="{
                                    'bg-rose-500 hover:bg-rose-600 text-white shadow-rose-500/30': confirm.type === 'danger',
                                    'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-500/30': confirm.type === 'warning',
                                    'bg-sky-500 hover:bg-sky-600 text-white shadow-sky-500/30': confirm.type === 'info',
                                    'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-500/30': confirm.type === 'success',
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