@php
    $userRole = Auth::user()->role ?? 'admin';
    if (in_array($userRole, ['admin', 'operator'])) {
        $pesanRoute = $userRole === 'admin' ? 'admin.pesan.index' : 'operator.pesan.index';
        $unreadPesan = \App\Models\Pesan::where('recipient_id', Auth::user()->id_anggota)->where('is_read', false)->count();
    }
@endphp

@if(in_array($userRole, ['admin', 'operator']))
    <!-- Floating Chat Widget -->
    <div id="floating-chat-widget" class="fixed z-50 flex items-center justify-center cursor-grab active:cursor-grabbing w-14 h-14 md:w-16 md:h-16" style="touch-action: none; right: 20px; bottom: 20px;">
        
        <a href="{{ route($pesanRoute) }}" class="relative group block w-full h-full draggable-prevent-click">
            <!-- Pulsing ring if unread -->
            @if($unreadPesan > 0)
                <div class="absolute inset-0 rounded-full bg-sky-400 opacity-40 animate-ping"></div>
            @endif

            <!-- Main Button -->
            <div class="absolute inset-0 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full shadow-lg shadow-sky-500/30 flex items-center justify-center transition-transform duration-300 group-hover:scale-105 border border-white/20 backdrop-blur-sm">
                
                <svg class="w-7 h-7 md:w-8 md:h-8 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>

                <!-- Badge -->
                @if($unreadPesan > 0)
                    <div class="absolute -top-1 -right-1 flex h-5 w-5 md:h-6 md:w-6">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex rounded-full h-full w-full bg-rose-500 border-2 border-white text-[10px] md:text-[11px] font-bold text-white items-center justify-center shadow-sm">
                            {{ $unreadPesan > 99 ? '99+' : $unreadPesan }}
                        </span>
                    </div>
                @endif
            </div>
            
            <!-- Tooltip (Hidden on Mobile) -->
            <div class="hidden md:block absolute -top-12 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                <div class="bg-slate-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                    Pesan Komunikasi
                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                </div>
            </div>
        </a>
    </div>

    <!-- Draggable Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const widget = document.getElementById('floating-chat-widget');
            let isDragging = false;
            let hasDragged = false;
            let currentX;
            let currentY;
            let initialX;
            let initialY;
            let xOffset = 0;
            let yOffset = 0;

            // Memuat posisi tersimpan
            const savedPos = localStorage.getItem('chatWidgetPos');
            if (savedPos) {
                const pos = JSON.parse(savedPos);
                xOffset = pos.x;
                yOffset = pos.y;
                setTranslate(xOffset, yOffset, widget);
            }
            
            // Jaga-jaga responsif saat window resize (kembalikan ke layar jika keluar batas)
            window.addEventListener('resize', () => {
                setTranslate(xOffset, yOffset, widget);
            });

            function dragStart(e) {
                if (e.type === "touchstart") {
                    initialX = e.touches[0].clientX - xOffset;
                    initialY = e.touches[0].clientY - yOffset;
                } else {
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                }

                if (e.target.closest('#floating-chat-widget')) {
                    isDragging = true;
                    hasDragged = false; 
                }
            }

            function dragEnd(e) {
                if (!isDragging) return;
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                
                localStorage.setItem('chatWidgetPos', JSON.stringify({ x: xOffset, y: yOffset }));
            }

            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    hasDragged = true;

                    if (e.type === "touchmove") {
                        currentX = e.touches[0].clientX - initialX;
                        currentY = e.touches[0].clientY - initialY;
                    } else {
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                    }

                    xOffset = currentX;
                    yOffset = currentY;

                    setTranslate(currentX, currentY, widget);
                }
            }

            function setTranslate(xPos, yPos, el) {
                // Kalkulasi batasan layar
                // Karena posisi dasar adalah (right: 20px, bottom: 20px) 
                // X Positif = Bergerak ke KIRI layar (karena right basisnya terbalik di CSS atau transform bekerja searah axis).
                // Wait, transformX positif bergerak ke KANAN. 
                // Karena elemen aslinya ada di right, batas kanannya adalah X <= 20
                // Batas kirinya adalah -(windowWidth - lebar widget - 20)
                
                const rect = el.getBoundingClientRect();
                const wWidth = window.innerWidth;
                const wHeight = window.innerHeight;
                
                // Menentukan titik absolut layar dari pergerakan saat ini untuk di-clamp
                // Default posisi elemen dari kanan bawah layar tanpa offset:
                const baseRight = 20; 
                const baseBottom = 20;
                const elWidth = el.offsetWidth;
                const elHeight = el.offsetHeight;

                // Batas minimum transform (kiri atas)
                const minX = - (wWidth - elWidth - baseRight);
                const minY = - (wHeight - elHeight - baseBottom);

                // Batas maksimum transform (kanan bawah)
                const maxX = baseRight; 
                const maxY = baseBottom;

                // Terapkan clamping agar tombol tidak tembus ke luar monitor/HP
                let clampedX = Math.max(minX, Math.min(xPos, maxX));
                let clampedY = Math.max(minY, Math.min(yPos, maxY));
                
                // Simpan hasil clamping kembali
                xOffset = clampedX;
                yOffset = clampedY;

                el.style.transform = `translate3d(${clampedX}px, ${clampedY}px, 0)`;
            }

            widget.addEventListener('click', function(e) {
                if (hasDragged) {
                    e.preventDefault();
                    hasDragged = false;
                }
            });

            widget.addEventListener("touchstart", dragStart, { passive: false });
            widget.addEventListener("touchend", dragEnd, { passive: false });
            widget.addEventListener("touchmove", drag, { passive: false });

            widget.addEventListener("mousedown", dragStart);
            document.addEventListener("mouseup", dragEnd);
            document.addEventListener("mousemove", drag);
        });
    </script>
@endif
