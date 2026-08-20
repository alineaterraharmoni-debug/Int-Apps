<div>
    {{-- Hero band --}}
    <div class="relative bg-navy dot-grid rounded-3xl px-5 md:px-8 py-7 md:py-9 mb-6 overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-sky/10 blur-2xl"></div>
        <div class="absolute right-16 bottom-0 w-24 h-24 rounded-full bg-amber/10 blur-2xl"></div>

        <div class="relative">
            <div class="text-[11px] font-mono uppercase tracking-widest text-white/40 mb-2">PT Alinea Terra Harmoni</div>
            <h1 class="font-display font-extrabold text-2xl md:text-3xl text-white mb-1">Pilih modul</h1>
            <p class="text-sm text-white/50 max-w-md">Satu superapp, empat cara kerja — data customer & opty yang sama dipakai di semua modul.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        @php
            $palette = [
                'sky' => ['bg' => 'bg-sky/10', 'text' => 'text-sky', 'bar' => 'bg-sky', 'ring' => 'group-hover:border-sky/40'],
                'teal' => ['bg' => 'bg-teal/10', 'text' => 'text-teal', 'bar' => 'bg-teal', 'ring' => 'group-hover:border-teal/40'],
                'amber' => ['bg' => 'bg-amber/10', 'text' => 'text-amber', 'bar' => 'bg-amber', 'ring' => 'group-hover:border-amber/40'],
                'violet' => ['bg' => 'bg-violet/10', 'text' => 'text-violet', 'bar' => 'bg-violet', 'ring' => 'group-hover:border-violet/40'],
            ];
        @endphp

        @foreach ($modules as $m)
            @php $c = $palette[$m['color']]; @endphp

            @if ($m['available'])
                <a href="{{ $m['route'] }}" class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 {{ $c['ring'] }} rounded-2xl p-4 md:p-5 flex flex-col items-center text-center overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <span class="absolute top-0 left-0 right-0 h-[3px] {{ $c['bar'] }}"></span>
            @else
                <div class="relative bg-white dark:bg-gray-800 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 md:p-5 flex flex-col items-center text-center overflow-hidden opacity-60">
            @endif
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-full {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center mb-3 transition-transform duration-200 {{ $m['available'] ? 'group-hover:scale-110' : '' }}">
                    <x-icon :name="$m['icon']" class="w-6 h-6" />
                </div>
                <div class="font-display font-bold text-sm md:text-base">{{ $m['name'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 mb-2.5">{{ $m['desc'] }}</div>
                @if ($m['available'])
                    <div class="text-[11px] font-mono {{ $c['text'] }} font-semibold">{{ $m['stat'] }}</div>
                @else
                    <div class="text-[10px] font-mono text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">{{ $m['stat'] }}</div>
                @endif
            @if ($m['available'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>
