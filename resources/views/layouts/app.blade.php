<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Opty Tracker' }} — Alinea Terra Harmoni</title>

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#131B33">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { ink: '#131B33', amber: '#F2A93B', sky: '#2AA9E0' },
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
    </style>
</head>
<body>
    <nav class="border-b border-gray-200 bg-white sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-display font-extrabold text-base md:text-lg">
                Opty <span class="text-amber">Tracker</span>
                <span class="hidden sm:inline text-xs font-mono text-gray-400 ml-2">Alinea Terra Harmoni</span>
            </a>
            {{-- Nav modul — cuma tampil di layar medium ke atas, di HP dipindah ke bottom nav --}}
            <div class="hidden md:flex gap-2 text-sm font-medium">
                <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('home') ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-100' }}">Home</a>
                <a href="{{ route('crm.board') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('crm.board') ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-100' }}">CRM</a>
                <a href="{{ route('crm.report') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('crm.report') ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-100' }}">Report</a>
            </div>
        </div>

        {{-- Submenu kontekstual: cuma muncul kalau lagi di dalam modul CRM, tampil di ATAS sesuai spek mobile --}}
        @if (request()->routeIs('crm.*'))
            <div class="max-w-7xl mx-auto px-4 md:px-6 pb-2 flex gap-1.5 overflow-x-auto text-sm">
                <a href="{{ route('crm.board') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium {{ request()->routeIs('crm.board') ? 'bg-sky-50 text-sky' : 'text-gray-500 hover:bg-gray-100' }}">
                    <i class="ti ti-layout-kanban text-sm align-middle"></i> Board
                </a>
                <a href="{{ route('crm.report') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium {{ request()->routeIs('crm.report') || request()->routeIs('crm.report.*') ? 'bg-sky-50 text-sky' : 'text-gray-500 hover:bg-gray-100' }}">
                    <i class="ti ti-chart-bar text-sm align-middle"></i> Report
                </a>
                <a href="{{ route('crm.customers') }}" class="px-3 py-1.5 rounded-full whitespace-nowrap font-medium {{ request()->routeIs('crm.customers') ? 'bg-sky-50 text-sky' : 'text-gray-500 hover:bg-gray-100' }}">
                    <i class="ti ti-building-store text-sm align-middle"></i> Customer Insight
                </a>
            </div>
        @endif
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-6">
        {{ $slot }}
    </main>

    {{-- Bottom tab nav (mobile) — daftar modul utama, submenu-nya sendiri tampil di atas --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 z-40" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-3">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('home') ? 'text-sky' : 'text-gray-400' }}">
                <i class="ti ti-home text-xl"></i> Home
            </a>
            <a href="{{ route('crm.board') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('crm.board') ? 'text-sky' : 'text-gray-400' }}">
                <i class="ti ti-users text-xl"></i> CRM
            </a>
            <a href="{{ route('crm.report') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium {{ request()->routeIs('crm.report') ? 'text-sky' : 'text-gray-400' }}">
                <i class="ti ti-chart-bar text-xl"></i> Report
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
