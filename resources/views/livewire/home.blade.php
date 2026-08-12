<div>
    <div class="mb-6">
        <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">PT Alinea Terra Harmoni</div>
        <h1 class="font-display font-extrabold text-xl md:text-2xl">Pilih modul</h1>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        @foreach ($modules as $m)
            @if ($m['available'])
                <a href="{{ $m['route'] }}" class="group bg-white border border-gray-200 rounded-2xl p-4 md:p-5 flex flex-col items-center text-center hover:border-sky hover:shadow-md transition">
            @else
                <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-5 flex flex-col items-center text-center opacity-50 cursor-not-allowed">
            @endif
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-full bg-sky-50 text-sky flex items-center justify-center mb-3 group-hover:bg-sky group-hover:text-white transition">
                    <i class="ti {{ $m['icon'] }} text-2xl"></i>
                </div>
                <div class="font-display font-bold text-sm md:text-base">{{ $m['name'] }}</div>
                <div class="text-xs text-gray-500 mt-0.5 mb-2">{{ $m['desc'] }}</div>
                <div class="text-[11px] font-mono text-gray-400">{{ $m['stat'] }}</div>
            @if ($m['available'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>
