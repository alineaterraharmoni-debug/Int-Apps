<div>
    {{-- Hero band --}}
    <div class="relative bg-navy dot-grid rounded-3xl px-5 md:px-8 py-6 md:py-8 mb-6 overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-sky/10 blur-2xl"></div>
        <div class="absolute right-16 bottom-0 w-24 h-24 rounded-full bg-amber/10 blur-2xl"></div>

        <div class="relative">
            <div class="text-[11px] font-mono uppercase tracking-widest text-white/40 mb-2">PT Alinea Terra Harmoni</div>
            <h1 class="font-display font-extrabold text-2xl md:text-3xl text-white mb-1">Halo, {{ $firstName }} 👋</h1>
            <p class="text-sm text-white/50 max-w-md">Satu superapp, data customer & opty yang sama dipakai di semua modul.</p>

            @if (($canCrm && $closingSoonCount > 0) || $quickAction)
                <div class="flex flex-wrap items-center gap-2 mt-3.5">
                    @if ($canCrm && $closingSoonCount > 0)
                        <div class="inline-flex items-center gap-1.5 bg-amber/15 border border-amber/25 text-amber text-xs font-medium rounded-full pl-2 pr-3 py-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber shrink-0"></span>
                            {{ $closingSoonCount }} opty target closing minggu ini
                        </div>
                    @endif

                    @if ($quickAction)
                        <a href="{{ $quickAction['url'] }}" class="inline-flex items-center gap-1.5 bg-sky text-navy text-xs font-semibold rounded-full pl-3 pr-3.5 py-2 hover:bg-sky/90 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            {{ $quickAction['label'] }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
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
                <div class="relative bg-white dark:bg-gray-800 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 md:p-5 flex flex-col items-center text-center overflow-hidden opacity-70">
            @endif
                <div class="w-12 h-12 md:w-14 md:h-14 rounded-full {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center mb-3 transition-transform duration-200 {{ $m['available'] ? 'group-hover:scale-110' : '' }}">
                    <x-icon :name="$m['icon']" class="w-6 h-6" />
                </div>
                <div class="font-display font-bold text-sm md:text-base">{{ $m['name'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 mb-2.5">{{ $m['desc'] }}</div>
                @if ($m['available'])
                    <div class="text-[11px] font-mono {{ $c['text'] }} font-semibold">{{ $m['stat'] }}</div>
                @else
                    <div class="text-[10px] font-mono text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">{{ $m['stat'] }}</div>
                @endif
            @if ($m['available'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>

    {{-- Modul yang belum dibangun — banner tipis, bukan kartu penuh, biar gak
         ngambil perhatian sama gede kayak modul yang beneran jalan. --}}
    @if (count($comingSoon))
        <div class="mt-3 md:mt-4 flex flex-wrap gap-2">
            @foreach ($comingSoon as $cs)
                <div class="inline-flex items-center gap-2 bg-white/60 dark:bg-gray-800/50 border border-dashed border-gray-300 dark:border-gray-700 rounded-full pl-2 pr-3.5 py-1.5 text-gray-500 dark:text-gray-400">
                    <span class="w-6 h-6 rounded-full bg-teal/10 text-teal flex items-center justify-center shrink-0">
                        <x-icon :name="$cs['icon']" class="w-3.5 h-3.5" />
                    </span>
                    <span class="text-xs font-medium">{{ $cs['name'] }}</span>
                    <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500">· segera hadir</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Summary Next Action yang belum di-checklist, per stage. Cuma keitung
         buat opty yang masih hidup (bukan Lost — Lost emang gak butuh
         checklist apa-apa). Cuma keliatan buat yang punya akses Board. --}}
    @if (count($checklistSummary))
        <div class="mt-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-display font-bold text-sm">Next Action Belum Di-checklist</div>
                <a href="{{ route('crm.board') }}" class="text-xs font-semibold text-sky">Buka Board →</a>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($checklistSummary as $row)
                    <div class="inline-flex items-center gap-1.5 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 text-rose-600 dark:text-rose-300 text-xs font-medium rounded-full pl-2 pr-3 py-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                        {{ $row['count'] }} opty {{ $row['stage'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
