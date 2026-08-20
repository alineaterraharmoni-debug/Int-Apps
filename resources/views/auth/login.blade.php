<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Opty Tracker</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0A1628">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#0A1628', amber: '#F6B01A', sky: '#19A9DB', teal: '#14B8A6', violet: '#8B5CF6' },
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
    <style>
        body{font-family:'Inter',sans-serif;}
        .dot-grid{
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 18px 18px;
        }
    </style>
</head>
<body class="min-h-screen bg-navy dot-grid flex items-center justify-center px-4">

    <div class="w-full max-w-sm">
        <div class="flex items-center justify-center gap-2.5 mb-8">
            <svg width="30" height="30" viewBox="0 0 26 26" fill="none" class="shrink-0">
                <line x1="13" y1="13" x2="4" y2="6" stroke="#19A9DB" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="22" y2="6" stroke="#14B8A6" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="4" y2="20" stroke="#F6B01A" stroke-width="1.3" opacity="0.7"/>
                <line x1="13" y1="13" x2="22" y2="20" stroke="#8B5CF6" stroke-width="1.3" opacity="0.7"/>
                <circle cx="4" cy="6" r="2.4" fill="#19A9DB"/>
                <circle cx="22" cy="6" r="2.4" fill="#14B8A6"/>
                <circle cx="4" cy="20" r="2.4" fill="#F6B01A"/>
                <circle cx="22" cy="20" r="2.4" fill="#8B5CF6"/>
                <circle cx="13" cy="13" r="3.2" fill="#0A1628"/>
            </svg>
            <span class="font-display font-extrabold text-xl text-white">Opty <span class="text-amber">Tracker</span></span>
        </div>

        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xl">
            <div class="mb-5">
                <h1 class="font-display font-extrabold text-lg text-ink">Masuk</h1>
                <p class="text-xs text-gray-500 mt-1">Khusus tim internal PT Alinea Terra Harmoni.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-100 text-red-600 text-sm rounded-lg px-3 py-2.5 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-500 block mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40 focus:border-sky">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 block mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky/40 focus:border-sky">
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-500">
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    Ingat saya
                </label>
                <button type="submit" class="w-full bg-navy text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-navysoft transition">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-[11px] font-mono text-white/30 mt-6">PT Alinea Terra Harmoni · Internal Superapp</p>
    </div>

</body>
</html>
