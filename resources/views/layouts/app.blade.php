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
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 min-w-0">
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
                <span class="font-display font-extrabold text-base md:text-lg text-white truncate">
                    Opty <span class="text-amber">Tracker</span>
                </span>
                <span class="hidden sm:inline text-[11px] font-mono text-white/40 ml-1 truncate">Alinea Terra Harmoni</span>
            </a>

            <div class="flex items-center gap-3 shrink-0">
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

                {{-- User chip + logout — selalu keliatan, mobile & desktop --}}
                @auth
                    <div class="flex items-center gap-2 pl-3 border-l border-white/10">
                        <div class="w-7 h-7 rounded-full bg-sky/20 text-sky flex items-center justify-center text-[11px] font-display font-bold shrink-0">
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
                        </div>
                        <span class="hidden md:inline text-xs text-white/60 max-w-[110px] truncate">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-white/40 hover:text-white/80 transition" title="Keluar">
                                <x-icon name="logout" class="w-[18px] h-[18px]" />
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Submenu kontekstual: cuma muncul kalau lagi di dalam modul CRM, tampil di ATAS sesuai spek mobile --}}
        @if (request()->routeIs('crm.*'))
            <div class="max-w-7xl mx-auto px-4 md:px-6 pb-2.5 flex gap-1.5 overflow-x-auto text-sm">
                <a href="{{ route('crm.board') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.board') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <x-icon name="layout-kanban" class="w-4 h-4" /> Board
                </a>
                <a href="{{ route('crm.report') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.report') || request()->routeIs('crm.report.*') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <x-icon name="chart-bar" class="w-4 h-4" /> Report
                </a>
                <a href="{{ route('crm.customers') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full whitespace-nowrap font-medium transition {{ request()->routeIs('crm.customers') ? 'bg-sky text-navy' : 'text-white/50 bg-white/5 hover:text-white/80' }}">
                    <x-icon name="building-store" class="w-4 h-4" /> Customer Insight
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
                <x-icon name="home" class="w-5 h-5 {{ request()->routeIs('home') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> Home
            </a>
            <a href="{{ route('crm.board') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.board') ? 'text-sky' : 'text-white/40' }}">
                <x-icon name="users" class="w-5 h-5 {{ request()->routeIs('crm.board') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> CRM
            </a>
            <a href="{{ route('crm.report') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.report') ? 'text-sky' : 'text-white/40' }}">
                <x-icon name="chart-bar" class="w-5 h-5 {{ request()->routeIs('crm.report') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> Report
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
