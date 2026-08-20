<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Opty Tracker' }} — Alinea Terra Harmoni</title>

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0A1628">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#131B33',
                        navy: '#0A1628',
                        navysoft: '#132540',
                        amber: '#F6B01A',
                        sky: '#19A9DB',
                        teal: '#14B8A6',
                        violet: '#8B5CF6',
                    },
                    fontFamily: {
                        display: ['Manrope', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2/dist/tabler-icons.min.css">
    @livewireStyles
    <style>
        body{font-family:'Inter',sans-serif;background:#F5F6F9;color:#131B33;}
        @media (max-width: 767px){
            body{ padding-bottom: 76px; } /* ruang buat bottom nav modul */
        }
        .dot-grid{
            background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 16px 16px;
        }
        .signal-dot{
            animation: signal-pulse 2.2s ease-in-out infinite;
        }
        @keyframes signal-pulse{
            0%, 100% { box-shadow: 0 0 0 0 rgba(25,169,219,0.55); }
            70% { box-shadow: 0 0 0 6px rgba(25,169,219,0); }
        }
        @media (prefers-reduced-motion: reduce){
            .signal-dot{ animation: none; }
        }
    </style>
</head>
<body>
    <nav class="bg-navy sticky top-0 z-40 dot-grid">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                {{-- Signature mark: 4 node terhubung ke 1 pusat — representasi modul superapp --}}
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" class="shrink-0">
                    <line x1="13" y1="13" x2="4" y2="6" stroke="#19A9DB" stroke-width="1.3" opacity="0.7"/>
                    <line x1="13" y1="13" x2="22" y2="6" stroke="#14B8A6" stroke-width="1.3" opacity="0.7"/>
                    <line x1="13" y1="13" x2="4" y2="20" stroke="#F6B01A" stroke-width="1.3" opacity="0.7"/>
                    <line x1="13" y1="13" x2="22" y2="20" stroke="#8B5CF6" stroke-width="1.3" opacity="0.7"/>
                    <circle cx="4" cy="6" r="2.4" fill="#19A9DB"/>
                    <circle cx="22" cy="6" r="2.4" fill="#14B8A6"/>
                    <circle cx="4" cy="20" r="2.4" fill="#F6B01A"/>
                    <circle cx="22" cy="20" r="2.4" fill="#8B5CF6"/>
                    <circle cx="13" cy="13" r="3.2" fill="#F5F6F9"/>
                </svg>
                <span class="font-display font-extrabold text-base md:text-lg text-white">
                    Opty <span class="text-amber">Tracker</span>
                </span>
                <span class="hidden sm:inline text-[11px] font-mono text-white/40 ml-1">Alinea Terra Harmoni</span>
            </a>
            {{-- Nav modul — cuma tampil di layar medium ke atas, di HP dipindah ke bottom nav --}}
            <div class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('home') ? 'bg-white/10 text-white' : 'text-white/50 hover:text-white/80' }}">Home</a>
                <a href="{{ route('crm.board') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('crm.board') ? 'bg-sky/15 text-sky' : 'text-white/50 hover:text-white/80' }}">CRM</a>
                <a href="{{ route('crm.report') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('crm.report') ? 'bg-sky/15 text-sky' : 'text-white/50 hover:text-white/80' }}">Report</a>
                <span class="w-px h-4 bg-white/10 mx-1"></span>
                <span class="flex items-center gap-1.5 text-[11px] font-mono text-white/40 pl-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 signal-dot"></span> Online
                </span>
            </div>
        </div>

        {{-- Submenu kontekstual: cuma muncul kalau lagi di dalam modul CRM, tampil di ATAS sesuai spek mobile --}}
        @if (request()->routeIs('crm.*'))
            <div class="max-w-7xl mx-auto px-4 md:px-6 pb-2.5 flex gap-1.5 overflow-x-auto text-sm">
                <a href="{{ route('crm.board') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.board') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <i class="ti ti-layout-kanban text-sm align-middle"></i> Board
                </a>
                <a href="{{ route('crm.report') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.report') || request()->routeIs('crm.report.*') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <i class="ti ti-chart-bar text-sm align-middle"></i> Report
                </a>
                <a href="{{ route('crm.customers') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.customers') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <i class="ti ti-building-store text-sm align-middle"></i> Customer Insight
                </a>
            </div>
        @endif
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-6">
        {{ $slot }}
    </main>

    {{-- Bottom tab nav (mobile) — daftar modul utama, submenu-nya sendiri tampil di atas --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-navy border-t border-white/10 z-40" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-3">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('home') ? 'text-sky' : 'text-white/40' }}">
                <i class="ti ti-home text-xl {{ request()->routeIs('home') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}"></i> Home
            </a>
            <a href="{{ route('crm.board') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.board') ? 'text-sky' : 'text-white/40' }}">
                <i class="ti ti-users text-xl {{ request()->routeIs('crm.board') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}"></i> CRM
            </a>
            <a href="{{ route('crm.report') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.report') ? 'text-sky' : 'text-white/40' }}">
                <i class="ti ti-chart-bar text-xl {{ request()->routeIs('crm.report') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}"></i> Report
            </a>
        </div>
    </nav>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
