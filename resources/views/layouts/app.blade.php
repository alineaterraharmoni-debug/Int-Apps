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
            darkMode: 'class',
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
    <script>
        // Default SELALU light — dark mode cuma nyala kalau user EKSPLISIT
        // toggle sendiri. Sengaja gak ikutin prefers-color-scheme HP, biar
        // gak bingung "kok defaultnya gelap" padahal belum disentuh.
        if (localStorage.getItem('opty-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body{font-family:'Inter',sans-serif;background:#F5F6F9;color:#131B33;}
        .dark body{background:#0B1220;color:#E5E7EB;}
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
        /* Alpine x-cloak: sembunyiin elemen sebelum Alpine selesai init, biar
           gak "kedip" nampilin konten yang belum di-toggle sama x-show. */
        [x-cloak] { display: none !important; }
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
                    @if (auth()->user()->hasPermission('crm.view'))
                        <a href="{{ route('crm.board') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('crm.board') ? 'bg-sky/15 text-sky' : 'text-white/50 hover:text-white/80' }}">CRM</a>
                    @endif
                    @if (auth()->user()->hasPermission('report.view'))
                        <a href="{{ route('crm.report') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('crm.report') ? 'bg-sky/15 text-sky' : 'text-white/50 hover:text-white/80' }}">Report</a>
                    @endif
                    @if (auth()->user()->hasPermission('document.view'))
                        <a href="{{ route('documents.index') }}" class="px-3 py-1.5 rounded-full transition {{ request()->routeIs('documents.*') ? 'bg-sky/15 text-sky' : 'text-white/50 hover:text-white/80' }}">Dokumen</a>
                    @endif
                    <span class="w-px h-4 bg-white/10 mx-1"></span>
                    <span class="flex items-center gap-1.5 text-[11px] font-mono text-white/40 pl-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 signal-dot"></span> Online
                    </span>
                </div>

                {{-- User chip → dropdown. Sebelumnya avatar + shield + key + moon + logout
                     numpuk semua dalam satu baris sempit (rawan salah tap di HP).
                     Sekarang cuma avatar yang keliatan, aksi lain masuk dropdown
                     dengan target tap yang lebih lega. --}}
                @auth
                    <div class="relative pl-3 border-l border-white/10" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 -m-1.5 p-1.5 rounded-full hover:bg-white/5 transition" :aria-expanded="open" aria-haspopup="true">
                            <div class="w-8 h-8 rounded-full bg-sky/20 text-sky flex items-center justify-center text-[11px] font-display font-bold shrink-0">
                                {{ collect(explode(' ', auth()->user()->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('') }}
                            </div>
                            <span class="hidden md:inline text-xs text-white/60 max-w-[110px] truncate">{{ auth()->user()->name }}</span>
                            <x-icon name="chevron-down" class="hidden md:block w-3.5 h-3.5 text-white/40 shrink-0 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl py-1.5 z-50"
                             style="display: none;">
                            <div class="px-3.5 py-2 border-b border-gray-100 dark:border-gray-700">
                                <div class="text-sm font-display font-bold text-ink dark:text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</div>
                            </div>

                            @if (auth()->user()->is_admin)
                                <a href="{{ route('accounts') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition">
                                    <x-icon name="shield" class="w-4 h-4 text-gray-400 shrink-0" /> Kelola Akun
                                </a>
                            @elseif (auth()->user()->hasPermission('accounts.create'))
                                <a href="{{ route('account.create') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition">
                                    <x-icon name="shield" class="w-4 h-4 text-gray-400 shrink-0" /> Tambah Akun Baru
                                </a>
                            @endif

                            <a href="{{ route('account.password') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition">
                                <x-icon name="key" class="w-4 h-4 text-gray-400 shrink-0" /> Ganti Password
                            </a>

                            <button id="themeToggle" type="button" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition text-left">
                                <x-icon name="moon" class="w-4 h-4 text-gray-400 shrink-0 theme-icon-dark" />
                                <x-icon name="sun" class="w-4 h-4 text-gray-400 shrink-0 theme-icon-light hidden" />
                                <span class="theme-icon-dark">Mode Gelap</span>
                                <span class="theme-icon-light hidden">Mode Terang</span>
                            </button>

                            <div class="border-t border-gray-100 dark:border-gray-700 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition text-left">
                                        <x-icon name="logout" class="w-4 h-4 shrink-0" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Submenu kontekstual: cuma muncul kalau lagi di dalam modul CRM, tampil di ATAS sesuai spek mobile.
             Grid rata (bukan scroll horizontal) — jumlah kolom nyesuain berapa tab yang keliatan.
             Tiap tab dikasih warna aksen sendiri (senada sama kartu di Home) biar lebih hidup
             dan gampang dibedain sekilas — bukan cuma satu warna sky monoton buat semua. --}}
        @if (request()->routeIs('crm.*'))
            @php
                $subTabConfig = [
                    'board' => ['route' => 'crm.board', 'active' => request()->routeIs('crm.board'), 'icon' => 'layout-kanban', 'label' => 'Board', 'perm' => 'crm.view', 'on' => 'bg-sky text-navy shadow-sm shadow-sky/30', 'off' => 'text-sky/60 bg-white/5 hover:text-sky hover:bg-sky/10'],
                    'report' => ['route' => 'crm.report', 'active' => request()->routeIs('crm.report') || request()->routeIs('crm.report.*'), 'icon' => 'chart-bar', 'label' => 'Report', 'perm' => 'report.view', 'on' => 'bg-violet text-white shadow-sm shadow-violet/30', 'off' => 'text-violet/60 bg-white/5 hover:text-violet hover:bg-violet/10'],
                    'customer' => ['route' => 'crm.customers', 'active' => request()->routeIs('crm.customers'), 'icon' => 'building-store', 'label' => 'Customer', 'perm' => 'customer.view', 'on' => 'bg-teal text-navy shadow-sm shadow-teal/30', 'off' => 'text-teal/60 bg-white/5 hover:text-teal hover:bg-teal/10'],
                    'team' => ['route' => 'crm.team', 'active' => request()->routeIs('crm.team'), 'icon' => 'users', 'label' => 'Tim', 'perm' => 'team.view', 'on' => 'bg-amber text-navy shadow-sm shadow-amber/30', 'off' => 'text-amber/60 bg-white/5 hover:text-amber hover:bg-amber/10'],
                ];
                $visibleTabs = collect($subTabConfig)->filter(fn ($t) => auth()->user()->hasPermission($t['perm']));
                $subTabs = max($visibleTabs->count(), 1);
            @endphp
            <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6 pb-2.5">
                <div class="grid gap-1.5" style="grid-template-columns: repeat({{ $subTabs }}, minmax(0, 1fr));">
                    @foreach ($visibleTabs as $tab)
                        <a href="{{ route($tab['route']) }}" class="flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 px-1.5 py-1.5 sm:py-1.5 rounded-lg sm:rounded-full font-semibold transition-all duration-150 text-center {{ $tab['active'] ? $tab['on'] : $tab['off'] }}">
                            <x-icon name="{{ $tab['icon'] }}" class="w-4 h-4 shrink-0" />
                            <span class="text-[10px] sm:text-sm leading-tight">{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </nav>


    <main class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-6">
        {{ $slot }}
    </main>

    {{-- Bottom tab nav (mobile) — daftar modul utama, submenu-nya sendiri tampil di atas --}}
    @php
        $bottomTabs = 1; // Home selalu ada
        if (auth()->user()->hasPermission('crm.view')) $bottomTabs++;
        if (auth()->user()->hasPermission('document.view')) $bottomTabs++;
        if (auth()->user()->hasPermission('report.view')) $bottomTabs++;
    @endphp
    <nav class="md:hidden fixed bottom-0 inset-x-0 bg-navy border-t border-white/10 z-40" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid" style="grid-template-columns: repeat({{ $bottomTabs }}, minmax(0, 1fr));">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('home') ? 'text-sky' : 'text-white/40' }}">
                <x-icon name="home" class="w-5 h-5 {{ request()->routeIs('home') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> Home
            </a>
            @if (auth()->user()->hasPermission('crm.view'))
                <a href="{{ route('crm.board') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.board') ? 'text-sky' : 'text-white/40' }}">
                    <x-icon name="users" class="w-5 h-5 {{ request()->routeIs('crm.board') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> CRM
                </a>
            @endif
            @if (auth()->user()->hasPermission('document.view'))
                <a href="{{ route('documents.index') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('documents.*') ? 'text-sky' : 'text-white/40' }}">
                    <x-icon name="file-text" class="w-5 h-5 {{ request()->routeIs('documents.*') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> Dokumen
                </a>
            @endif
            @if (auth()->user()->hasPermission('report.view'))
                <a href="{{ route('crm.report') }}" class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium transition {{ request()->routeIs('crm.report') ? 'text-sky' : 'text-white/40' }}">
                    <x-icon name="chart-bar" class="w-5 h-5 {{ request()->routeIs('crm.report') ? 'drop-shadow-[0_0_6px_rgba(25,169,219,0.6)]' : '' }}" /> Report
                </a>
            @endif
        </div>
    </nav>

    <div id="installBanner" class="hidden md:hidden fixed bottom-[76px] inset-x-3 z-50 bg-navy border border-white/10 rounded-xl px-4 py-3 items-center justify-between gap-3 shadow-xl">
        <div class="flex items-center gap-2.5 min-w-0">
            <svg width="20" height="20" viewBox="0 0 26 26" fill="none" class="shrink-0">
                <circle cx="4" cy="6" r="2.4" fill="#19A9DB"/>
                <circle cx="22" cy="6" r="2.4" fill="#14B8A6"/>
                <circle cx="4" cy="20" r="2.4" fill="#F6B01A"/>
                <circle cx="22" cy="20" r="2.4" fill="#8B5CF6"/>
                <circle cx="13" cy="13" r="3.2" fill="#0A1628"/>
                <line x1="13" y1="13" x2="4" y2="6" stroke="#19A9DB" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="22" y2="6" stroke="#14B8A6" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="4" y2="20" stroke="#F6B01A" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="22" y2="20" stroke="#8B5CF6" stroke-width="1.3" opacity="0.7"/>
            </svg>
            <span class="text-xs text-white/80 truncate">Install Opty Tracker ke layar utama HP lo</span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button id="installDismiss" class="text-white/40 text-xs px-2">Nanti</button>
            <button id="installBtn" class="bg-sky text-navy text-xs font-semibold px-3 py-1.5 rounded-lg">Install</button>
        </div>
    </div>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        // Custom install prompt — browser modern (Chrome/Edge Android) gak nongolin
        // popup otomatis lagi, jadi kita tangkep event-nya dan tawarin tombol sendiri.
        let deferredPrompt = null;
        const banner = document.getElementById('installBanner');
        const dismissedKey = 'opty-install-dismissed';

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (!localStorage.getItem(dismissedKey) && banner) {
                banner.classList.remove('hidden');
                banner.classList.add('flex');
            }
        });

        document.getElementById('installBtn')?.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            banner.classList.add('hidden');
        });

        document.getElementById('installDismiss')?.addEventListener('click', () => {
            localStorage.setItem(dismissedKey, '1');
            banner.classList.add('hidden');
        });

        window.addEventListener('appinstalled', () => {
            banner?.classList.add('hidden');
        });

        // Dark/light toggle
        function syncThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.theme-icon-dark').forEach(el => el.classList.toggle('hidden', isDark));
            document.querySelectorAll('.theme-icon-light').forEach(el => el.classList.toggle('hidden', !isDark));
        }
        syncThemeIcon();

        document.getElementById('themeToggle')?.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('opty-theme', isDark ? 'dark' : 'light');
            syncThemeIcon();
        });
    </script>
</body>
</html>
