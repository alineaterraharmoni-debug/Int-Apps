<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Opty Tracker' }} — Alinea Terra Harmoni</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#131B33',
                        amber: '#F2A93B',
                        sky: '#2AA9E0',
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
    <style>body{font-family:'Inter',sans-serif;background:#F5F6F9;color:#131B33;}</style>
</head>
<body>
    <nav class="border-b border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
            <div class="font-display font-extrabold text-lg">
                Opty <span class="text-amber">Tracker</span>
                <span class="text-xs font-mono text-gray-400 ml-2">Alinea Terra Harmoni</span>
            </div>
            <div class="flex gap-2 text-sm font-medium">
                <a href="{{ route('board') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('board') ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-100' }}">Board</a>
                <a href="{{ route('report') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('report') ? 'bg-ink text-white' : 'text-gray-600 hover:bg-gray-100' }}">Report</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
