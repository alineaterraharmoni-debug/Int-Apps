<div>
    <div class="flex items-center justify-between mb-4 md:mb-5">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Pipeline Opty</h1>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-xs">
                <button wire:click="setViewMode('board')" class="px-2.5 py-1.5 rounded-md font-medium flex items-center gap-1 {{ $viewMode === 'board' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                    <x-icon name="layout-kanban" class="w-3.5 h-3.5" /> <span class="hidden sm:inline">Board</span>
                </button>
                <button wire:click="setViewMode('list')" class="px-2.5 py-1.5 rounded-md font-medium flex items-center gap-1 {{ $viewMode === 'list' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                    <x-icon name="file-text" class="w-3.5 h-3.5" /> <span class="hidden sm:inline">List</span>
                </button>
            </div>
            @if ($canCreateOrEdit)
                <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                    + Opty Baru
                </button>
            @else
                <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">Mode lihat aja</span>
            @endif
        </div>
    </div>

    @if ($viewMode === 'board')
    <div class="text-[11px] text-gray-400 dark:text-gray-500 mb-2 md:hidden">
        Geser ke samping buat lihat stage lainnya → · Tap kartu buat ubah stage/detail
    </div>

    {{-- Search di Board — filter kartu di SEMUA kolom sekaligus, jadi gak perlu
         scroll manual satu-satu kolom buat nyari opty/customer tertentu. --}}
    <div class="relative mb-3">
        <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input type="text" wire:model.live.debounce.400ms="boardSearch" placeholder="Cari opty atau customer di semua kolom..." class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg pl-9 pr-3 py-2.5 text-sm">
    </div>

    <div
        class="flex md:grid md:grid-cols-4 gap-3 overflow-x-auto md:overflow-visible snap-x snap-mandatory -mx-3 px-3 md:mx-0 md:px-0 pb-2"
        x-data="{}"
    >
        @php
            $stageColors = [
                'leads' => ['bar' => 'bg-slate-400', 'text' => 'text-slate-500 dark:text-slate-300', 'bg' => 'bg-slate-50 dark:bg-slate-500/10', 'border' => 'border-slate-200 dark:border-slate-500/30'],
                'develop' => ['bar' => 'bg-blue-400', 'text' => 'text-blue-600 dark:text-blue-300', 'bg' => 'bg-blue-50 dark:bg-blue-500/10', 'border' => 'border-blue-200 dark:border-blue-500/30'],
                'won' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-300', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'border' => 'border-emerald-200 dark:border-emerald-500/30'],
                'lost' => ['bar' => 'bg-rose-500', 'text' => 'text-rose-600 dark:text-rose-300', 'bg' => 'bg-rose-50 dark:bg-rose-500/10', 'border' => 'border-rose-200 dark:border-rose-500/30'],
            ];
        @endphp
        @foreach ($stages as $key => $label)
            @php
                $items = $grouped[$key] ?? collect();
                $sc = $stageColors[$key] ?? $stageColors['leads'];
            @endphp
            <div
                class="{{ $sc['bg'] }} border-2 {{ $sc['border'] }} rounded-2xl p-3 min-h-[140px] shrink-0 w-[82vw] sm:w-[60vw] md:w-auto snap-start relative overflow-hidden"
                x-on:dragover.prevent="$el.classList.add('ring-2','ring-sky-400')"
                x-on:dragleave="$el.classList.remove('ring-2','ring-sky-400')"
                x-on:drop.prevent="
                    $el.classList.remove('ring-2','ring-sky-400');
                    $wire.moveStage(parseInt($event.dataTransfer.getData('text/plain')), '{{ $key }}')
                "
            >
                <span class="absolute top-0 left-0 right-0 h-1 {{ $sc['bar'] }}"></span>
                <div class="flex items-center justify-between mb-3 px-1 pt-1">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $sc['bar'] }}"></span>
                            <div class="font-display font-bold text-sm {{ $sc['text'] }}">{{ $label }}</div>
                        </div>
                        <div class="text-xs font-mono text-gray-400 dark:text-gray-500 ml-3.5">
                            Rp {{ number_format($items->sum('tcv'), 0, ',', '.') }}
                        </div>
                    </div>
                    <span class="text-xs font-mono bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">
                        {{ $items->count() }}
                    </span>
                </div>

                {{-- Kartu di-scroll internal per kolom (bukan kolomnya yang manjang ke
                     bawah) begitu isinya banyak — kombinasi sama sorting prioritas di
                     atas (rating tinggi & closing paling deket duluan), jadi kolom
                     penuh tetep kelola-able tanpa perlu warning apa-apa. --}}
                <div class="max-h-[62vh] md:max-h-[65vh] overflow-y-auto flex flex-col gap-2.5 divide-y-2 divide-gray-300 dark:divide-gray-600 pr-0.5 -mr-0.5">
                @forelse ($items as $opty)
                    <div
                        class="opty-card"
                        style="touch-action: manipulation;"
                        x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $opty->id }}')"
                        wire:click="openDetail({{ $opty->id }})"
                        wire:key="opty-{{ $opty->id }}"
                        @class([
                            'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 transition cursor-pointer hover:shadow-md',
                            'ring-1 ring-rose-300 dark:ring-rose-500/40' => $opty->is_overdue,
                        ])
                    >
                        <div class="font-display font-bold text-sm leading-snug">{{ $opty->title }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $opty->customer?->name ?? $opty->customer_name }}</div>

                        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                {{ $opty->category_label }}
                            </span>
                            <span @class([
                                'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' => $opty->rating === 'high',
                                'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $opty->rating === 'med',
                                'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                            ])>
                                {{ $opty->rating_label }}
                            </span>
                            @if ($opty->hasPendingChecklist())
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                    Action
                                </span>
                            @endif
                        </div>

                        <div class="font-mono font-semibold text-sm mb-1">
                            Rp {{ number_format($opty->tcv, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 dark:text-gray-500 font-mono mb-1">
                            GP {{ rtrim(rtrim(number_format($opty->gp_percentage, 1), '0'), '.') }}% ·
                            Rp {{ number_format($opty->gp_nominal, 0, ',', '.') }}
                        </div>

                        <div class="flex items-center justify-between text-[11px] mt-2">
                            <span class="text-gray-400 dark:text-gray-500">{{ $opty->sales?->name ?? '—' }}</span>
                            @if ($opty->is_overdue)
                                <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 font-semibold">
                                    <x-icon name="alert" class="w-3 h-3" />
                                    Telat {{ $opty->expected_closing_date->diffInDays(now()) }} hr
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">{{ $opty->expected_closing_date?->format('d M Y') ?? '—' }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-xs text-gray-400 dark:text-gray-500 py-6">
                        {{ $boardSearch ? 'Gak ada yang cocok' : 'Belum ada opty' }}
                    </div>
                @endforelse
                </div>
            </div>
        @endforeach
    </div>
    @else
        {{-- ===== List View ===== --}}
        @php
            $activeFilterCount = collect([$listFilterStage, $listFilterRating, $listFilterCategory, $listFilterSales, $listFilterCustomer])
                ->filter(fn ($v) => $v !== '' && $v !== null)
                ->count();
            $sortIcon = fn (string $col) => $listSortBy === $col ? 'text-sky' : 'text-gray-300 dark:text-gray-600';
        @endphp

        <div class="flex items-center gap-2 mb-3">
            <div class="relative flex-1 min-w-0">
                <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" wire:model.live.debounce.400ms="listSearch" placeholder="Cari nama opty / customer..." class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg pl-9 pr-3 py-2.5 text-sm">
            </div>
            <button wire:click="$toggle('showListFilters')" class="relative shrink-0 inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showListFilters ? 'bg-gray-100 dark:bg-gray-700' : 'bg-white dark:bg-gray-800' }}">
                <x-icon name="sliders" class="w-4 h-4" />
                <span class="hidden sm:inline">Filter</span>
                @if ($activeFilterCount)
                    <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
                @endif
            </button>
            <a href="{{ route('crm.board.export', ['stage' => $listFilterStage, 'rating' => $listFilterRating]) }}"
               class="shrink-0 inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
               title="Export ke Excel (CSV)">
                <x-icon name="download" class="w-4 h-4" />
                <span class="hidden sm:inline">Export</span>
            </a>
        </div>

        @if ($showListFilters)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Stage</label>
                    <select wire:model.live="listFilterStage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($stages as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Rating</label>
                    <select wire:model.live="listFilterRating" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($ratings as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Kategori</label>
                    <select wire:model.live="listFilterCategory" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($categories as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Sales</label>
                    <select wire:model.live="listFilterSales" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($salesOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Customer</label>
                    <select wire:model.live="listFilterCustomer" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($customerOptions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Per Halaman</label>
                    <select wire:model.live="listPerPage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                @if ($activeFilterCount)
                    <div class="col-span-2 md:col-span-3 pt-1">
                        <button wire:click="resetListFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                            <x-icon name="x" class="w-3 h-3" /> Reset semua filter
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Tabel penuh cuma ditampilin di tablet/desktop (md+) — di layar sempit,
             4 kolom tabel gak muat tanpa scroll horizontal yang kurang enak dipakai
             (nama opty/customer kepotong). Solusinya di HP: card list bertumpuk,
             semua info kebaca penuh tanpa geser ke samping. --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                        <th class="p-3 cursor-pointer select-none" wire:click="sortList('title')">
                            <span class="inline-flex items-center gap-1">Nama Opty <x-icon name="arrow-up-down" class="w-3 h-3 {{ $sortIcon('title') }}" /></span>
                        </th>
                        <th class="p-3 cursor-pointer select-none" wire:click="sortList('customer')">
                            <span class="inline-flex items-center gap-1">Customer <x-icon name="arrow-up-down" class="w-3 h-3 {{ $sortIcon('customer') }}" /></span>
                        </th>
                        <th class="p-3 cursor-pointer select-none" wire:click="sortList('stage')">
                            <span class="inline-flex items-center gap-1">Stage <x-icon name="arrow-up-down" class="w-3 h-3 {{ $sortIcon('stage') }}" /></span>
                        </th>
                        <th class="p-3 cursor-pointer select-none" wire:click="sortList('rating')">
                            <span class="inline-flex items-center gap-1">Rating <x-icon name="arrow-up-down" class="w-3 h-3 {{ $sortIcon('rating') }}" /></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listItems as $opty)
                        <tr
                            wire:key="list-opty-{{ $opty->id }}"
                            wire:click="openDetail({{ $opty->id }})"
                            class="border-b border-gray-50 dark:border-gray-700/60 transition cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40"
                        >
                            <td class="p-3 font-medium">
                                @if ($opty->is_overdue)
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5" title="Closing kelewat"></span>
                                @endif
                                @if ($opty->hasPendingChecklist())
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5" title="Next Action belum di-checklist"></span>
                                @endif
                                {{ $opty->title }}
                            </td>
                            <td class="p-3 text-gray-500 dark:text-gray-400">{{ $opty->customer?->name ?? $opty->customer_name }}</td>
                            <td class="p-3">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $opty->stage_label }}</span>
                            </td>
                            <td class="p-3">
                                <span @class([
                                    'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                    'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' => $opty->rating === 'high',
                                    'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $opty->rating === 'med',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                                ])>{{ $opty->rating_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada opty untuk filter ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card list — versi mobile, gantiin tabel biar gak ada scroll horizontal.
             Sort tetep jalan lewat tombol/filter yang sama, cuma tampilannya beda. --}}
        <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
            @forelse ($listItems as $opty)
                <div
                    wire:key="list-opty-mobile-{{ $opty->id }}"
                    wire:click="openDetail({{ $opty->id }})"
                    class="p-3.5 transition cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40 active:bg-gray-100 dark:active:bg-gray-700/60"
                >
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="font-medium text-sm leading-snug flex items-start gap-1.5 min-w-0">
                            @if ($opty->is_overdue)
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5 shrink-0" title="Closing kelewat"></span>
                            @endif
                            @if ($opty->hasPendingChecklist())
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0" title="Next Action belum di-checklist"></span>
                            @endif
                            <span class="truncate">{{ $opty->title }}</span>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky shrink-0">{{ $opty->stage_label }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $opty->customer?->name ?? $opty->customer_name }}</span>
                        <span @class([
                            'text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0',
                            'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' => $opty->rating === 'high',
                            'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $opty->rating === 'med',
                            'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                        ])>{{ $opty->rating_label }}</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada opty untuk filter ini</div>
            @endforelse
        </div>

        {{-- Total TCV — ngikutin filter/search yang lagi aktif, dihitung dari
             SEMUA opty yang match (bukan cuma yang kelihatan di halaman ini),
             jadi tetep akurat walau lagi di-paginate. --}}
        <div class="flex items-center justify-between bg-ink dark:bg-gray-900 text-white rounded-2xl px-4 py-3 mt-3">
            <span class="text-xs font-medium text-white/60">
                Total TCV
                @if ($activeFilterCount || $listSearch)
                    <span class="text-white/40">(sesuai filter)</span>
                @endif
            </span>
            <span class="font-display font-bold text-sm md:text-base">Rp {{ number_format($listTotalTcv, 0, ',', '.') }}</span>
        </div>

        <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
            <div class="text-xs text-gray-400 dark:text-gray-500">
                Nampilin {{ $listItems->firstItem() ?? 0 }}–{{ $listItems->lastItem() ?? 0 }} dari {{ $listItems->total() }} opty
            </div>
            <div>
                {{ $listItems->links() }}
            </div>
        </div>
    @endif

    {{-- Modal View Detail (read-only) — muncul pas klik kartu/nama opty,
         siapapun boleh liat termasuk role view-only. Edit dipindah jadi
         tombol DI DALEM sini, cuma muncul kalau role-nya emang boleh edit
         opty ini spesifik (ngikutin aturan Leads-only kalau role-nya kebatas). --}}
    @if ($showDetailModal && $detailOpty)
        @php
            $detailEditable = $canManageFull || ($canManageMqlOnly && $detailOpty->stage === 'leads');
            $detailStageColors = [
                'leads' => 'bg-slate-50 dark:bg-slate-500/10 text-slate-600 dark:text-slate-300',
                'develop' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-300',
                'won' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-300',
                'lost' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300',
            ];
        @endphp
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-2 mb-4">
                    <div>
                        <h2 class="font-display font-extrabold text-lg leading-snug">{{ $detailOpty->title }}</h2>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $detailOpty->customer?->name ?? $detailOpty->customer_name }}</div>
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none shrink-0">&times;</button>
                </div>

                <div class="flex items-center gap-1.5 flex-wrap mb-4">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $detailStageColors[$detailOpty->stage] ?? '' }}">{{ $detailOpty->stage_label }}</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">{{ $detailOpty->category_label }}</span>
                    <span @class([
                        'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                        'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' => $detailOpty->rating === 'high',
                        'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $detailOpty->rating === 'med',
                        'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $detailOpty->rating === 'low',
                    ])>{{ $detailOpty->rating_label }}</span>
                    @if ($detailOpty->is_overdue)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">Closing Kelewat</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">TCV</div>
                        <div class="font-mono font-bold text-sm">Rp {{ number_format($detailOpty->tcv, 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">GP</div>
                        <div class="font-mono font-bold text-sm">{{ rtrim(rtrim(number_format($detailOpty->gp_percentage, 1), '0'), '.') }}% · Rp {{ number_format($detailOpty->gp_nominal, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="space-y-3 text-sm mb-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Sales</div>
                            <div>{{ $detailOpty->sales?->name ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Presales</div>
                            <div>{{ $detailOpty->presales?->name ?: '—' }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Tim Engineer</div>
                        <div>{{ $detailOpty->engineers->pluck('name')->implode(', ') ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Ekspektasi Closing</div>
                        <div>{{ $detailOpty->expected_closing_date?->format('d M Y') ?? '—' }}</div>
                    </div>

                    @if ($detailOpty->stage === 'won')
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Alasan Menang</div>
                            <div>{{ $detailOpty->won_category_label ?? '—' }}{{ $detailOpty->won_reason ? ' — '.$detailOpty->won_reason : '' }}</div>
                        </div>
                    @endif
                    @if ($detailOpty->stage === 'lost')
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Alasan Drop</div>
                            <div>{{ $detailOpty->lost_category_label ?? '—' }}{{ $detailOpty->lost_reason ? ' — '.$detailOpty->lost_reason : '' }}</div>
                        </div>
                    @endif

                    @php $nextActionItemsDetail = \App\Models\Opportunity::NEXT_ACTION_ITEMS[$detailOpty->stage] ?? []; @endphp
                    @if (count($nextActionItemsDetail))
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Next Action</div>
                            <div class="space-y-1">
                                @foreach ($nextActionItemsDetail as $key => $label)
                                    @php $checked = ! empty($detailOpty->next_action_checklist[$key] ?? null); @endphp
                                    <div class="flex items-center gap-1.5 text-xs">
                                        @if ($checked)
                                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4"><path d="M20 6 9 17l-5-5"/></svg>
                                            </span>
                                        @else
                                            <span class="w-3.5 h-3.5 rounded-full border-2 border-gray-300 dark:border-gray-600 shrink-0"></span>
                                        @endif
                                        <span class="{{ $checked ? 'text-gray-500 dark:text-gray-400 line-through' : '' }}">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($detailOpty->notes)
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Catatan</div>
                            <div>{{ $detailOpty->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="closeDetail" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Tutup</button>
                    @if ($detailEditable)
                        <button wire:click="editFromDetail({{ $detailOpty->id }})" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Edit</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Form (create/edit) --}}
    @if ($showModal)
        @php
            $infoHasError = $errors->hasAny(['title', 'customer_id', 'new_customer_name', 'new_customer_address', 'category', 'tcv', 'gp_percentage', 'rating']);
            $stageHasError = $errors->hasAny(['stage', 'expected_closing_date', 'lost_category', 'lost_reason', 'won_category', 'won_reason']);
            $timHasError = $errors->hasAny(['sales_id', 'presales_id', 'engineer_ids']);
            $catatanHasError = $errors->hasAny(['notes']);
        @endphp
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div
                class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-xl max-h-[92vh] sm:max-h-[88vh] flex flex-col overflow-hidden"
                x-data="{ tab: @entangle('activeTab').defer }"
            >
                <div class="flex items-center justify-between px-4 sm:px-6 pt-4 sm:pt-5 pb-3 shrink-0">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Opty' : 'Opty Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>

                {{-- Modal dipecah jadi 4 tab (Info/Stage/Tim/Catatan) biar gak jadi satu
                     scroll super panjang di HP. Tab switch murni di Alpine (gak nunggu
                     server), tapi semua field TETEP ada di DOM (cuma disembunyiin pake
                     x-show), jadi wire:model & validasi tetep jalan normal apapun tab
                     yang lagi aktif. --}}
                <div class="flex gap-1 px-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 shrink-0 overflow-x-auto">
                    <button type="button" x-on:click="tab = 'info'" :class="tab === 'info' ? 'border-sky text-sky' : 'border-transparent text-gray-400 dark:text-gray-500'" class="relative px-3 py-2.5 text-sm font-semibold border-b-2 whitespace-nowrap transition">
                        Info Utama
                        @if ($infoHasError)<span class="absolute top-1.5 right-0 w-1.5 h-1.5 rounded-full bg-rose-500"></span>@endif
                    </button>
                    <button type="button" x-on:click="tab = 'stage'" :class="tab === 'stage' ? 'border-sky text-sky' : 'border-transparent text-gray-400 dark:text-gray-500'" class="relative px-3 py-2.5 text-sm font-semibold border-b-2 whitespace-nowrap transition">
                        Stage & Closing
                        @if ($stageHasError)<span class="absolute top-1.5 right-0 w-1.5 h-1.5 rounded-full bg-rose-500"></span>@endif
                    </button>
                    <button type="button" x-on:click="tab = 'tim'" :class="tab === 'tim' ? 'border-sky text-sky' : 'border-transparent text-gray-400 dark:text-gray-500'" class="relative px-3 py-2.5 text-sm font-semibold border-b-2 whitespace-nowrap transition">
                        Tim
                        @if ($timHasError)<span class="absolute top-1.5 right-0 w-1.5 h-1.5 rounded-full bg-rose-500"></span>@endif
                    </button>
                    <button type="button" x-on:click="tab = 'catatan'" :class="tab === 'catatan' ? 'border-sky text-sky' : 'border-transparent text-gray-400 dark:text-gray-500'" class="relative px-3 py-2.5 text-sm font-semibold border-b-2 whitespace-nowrap transition">
                        Catatan
                        @if ($catatanHasError)<span class="absolute top-1.5 right-0 w-1.5 h-1.5 rounded-full bg-rose-500"></span>@endif
                    </button>
                </div>

                @if (count($missingFieldsNotice))
                    <div class="mx-4 sm:mx-6 mt-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-3 py-2.5 text-xs text-amber-800 dark:text-amber-300 shrink-0">
                        <span class="font-semibold">Lengkapi dulu sebelum lanjut:</span>
                        {{ collect($missingFieldsNotice)->map(fn ($f) => $missingFieldsLabels[$f] ?? $f)->implode(', ') }}
                    </div>
                @endif

                <form wire:submit="save" class="flex flex-col overflow-hidden flex-1">
                    <div class="overflow-y-auto px-4 sm:px-6 py-4 space-y-4 flex-1">

                        {{-- ===== TAB: Info Utama ===== --}}
                        <div x-show="tab === 'info'" x-cloak>
                            <div class="mb-4">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Judul Opty <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="title" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm" placeholder="cth. Firewall Renewal - Bank XYZ">
                                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Customer</label>
                                    @if (! $showQuickAddCustomer)
                                        <div
                                            class="relative"
                                            x-data="{
                                                open: false,
                                                q: {{ \Illuminate\Support\Js::from($customer_id ? optional($customerOptions->firstWhere('id', $customer_id))->name : '') }},
                                                items: {{ \Illuminate\Support\Js::from($customerOptions->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values()) }},
                                                get filtered() {
                                                    if (! this.q) return this.items;
                                                    const s = this.q.toLowerCase();
                                                    return this.items.filter(c => c.name.toLowerCase().includes(s));
                                                },
                                                pick(c) { this.q = c.name; this.open = false; $wire.set('customer_id', c.id, false); }
                                            }"
                                            x-on:click.outside="open = false"
                                        >
                                            <input type="text" x-model="q" x-on:focus="open = true" x-on:click="open = true"
                                                   x-on:input="open = true; $wire.set('customer_id', null, false)"
                                                   placeholder="Ketik buat cari customer..." autocomplete="off"
                                                   class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                            <div x-show="open" x-cloak style="display:none;" class="absolute z-30 mt-1 w-full max-h-44 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                                <template x-for="c in filtered" :key="c.id">
                                                    <div x-on:click="pick(c)" x-text="c.name" class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"></div>
                                                </template>
                                                <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">Gak ketemu — coba "+ Customer baru" di bawah</div>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('showQuickAddCustomer', true)" class="text-[11px] text-sky font-semibold mt-1">
                                            + Customer baru
                                        </button>
                                    @else
                                        <div class="space-y-1.5">
                                            <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Nama Customer Baru <span class="text-rose-500">*</span></label>
                                            <input type="text" wire:model="new_customer_name" placeholder="Nama customer" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                            @error('new_customer_name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                            <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 block">Alamat <span class="text-rose-500">*</span></label>
                                            <textarea wire:model="new_customer_address" rows="2" placeholder="Alamat lengkap customer" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                                            @error('new_customer_address') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                            <div class="flex gap-1.5">
                                                <button type="button" wire:click="quickAddCustomer" class="flex-1 text-xs font-semibold px-3 py-2 rounded-lg bg-ink text-white">Simpan Customer</button>
                                                <button type="button" wire:click="$set('showQuickAddCustomer', false)" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 whitespace-nowrap">Batal</button>
                                            </div>
                                        </div>
                                    @endif
                                    @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Lini Produk <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="category" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                        @foreach ($categories as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div
                                x-data="{
                                    tcv: {{ (int) ($tcv !== '' && $tcv !== null ? $tcv : 0) }},
                                    tcvDisplay: '',
                                    gp: {{ $gp_percentage !== '' && $gp_percentage !== null ? (float) $gp_percentage : 0 }},
                                    fmt() { this.tcvDisplay = this.tcv ? this.tcv.toLocaleString('id-ID') : ''; },
                                    get gpNominal() { return Math.round(this.tcv * (parseFloat(this.gp) || 0) / 100); }
                                }"
                                x-init="fmt()"
                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4"
                            >
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Estimasi TCV (Rp) <span class="text-rose-500">*</span></label>
                                    <input type="text" inputmode="numeric" x-model="tcvDisplay"
                                           x-on:input="tcv = parseInt($event.target.value.replace(/\D/g,'')) || 0; fmt(); $wire.set('tcv', tcv, false)"
                                           placeholder="0" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    @error('tcv') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">GP (% dari TCV) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model="gp_percentage"
                                           x-on:input="gp = $event.target.value"
                                           class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    @error('gp_percentage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2 flex items-center justify-between bg-gray-50 dark:bg-gray-900/40 rounded-lg px-3 py-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Estimasi GP Nominal</span>
                                    <span class="font-mono font-semibold text-sm" x-text="'Rp ' + gpNominal.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Rating <span class="text-rose-500">*</span></label>
                                <select wire:model="rating" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    @foreach ($ratings as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ===== TAB: Stage & Closing ===== --}}
                        <div x-show="tab === 'stage'" x-cloak>
                            <div class="{{ in_array($stage, ['develop', 'won'], true) ? 'grid grid-cols-1 sm:grid-cols-2 gap-3' : '' }} mb-1">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Stage</label>
                                    @if ($canManageFull)
                                        <select wire:model.live="stage" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                            @foreach ($stages as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" value="Leads" disabled class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500">
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Role lo dibatasin cuma sampai stage Leads.</p>
                                    @endif
                                </div>

                                {{-- Ekspektasi Closing cuma relevan begitu opty udah masuk Develop —
                                     di Leads/Lost field ini disembunyiin total, bukan cuma ditandain
                                     opsional, biar form-nya beneran nyesuain stage. --}}
                                @if (in_array($stage, ['develop', 'won'], true))
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">
                                            Ekspektasi Closing <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="date" wire:model="expected_closing_date" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                        @error('expected_closing_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>

                            @if ($stage === 'leads')
                                <p class="text-xs text-gray-400 dark:text-gray-500 italic mb-4">Ekspektasi Closing baru muncul & wajib diisi begitu opty ini naik ke stage Develop.</p>
                            @else
                                <div class="mb-4"></div>
                            @endif

                            @if ($stage === 'lost')
                                <div class="bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 rounded-xl p-3">
                                    @if ($promptingLostReason)
                                        <p class="text-xs font-semibold text-rose-700 dark:text-rose-300 mb-2">
                                            Mau dipindah ke Lost — isi alasannya dulu, baru bisa disimpan & beneran pindah stage.
                                        </p>
                                    @endif
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Kategori Alasan Drop</label>
                                    <select wire:model="lost_category" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm mb-2">
                                        <option value="">— pilih alasan —</option>
                                        @foreach ($lostCategories as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('lost_category') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Detail Tambahan (opsional)</label>
                                    <textarea wire:model="lost_reason" rows="2" placeholder="cth. Customer pilih kompetitor karena harga 15% lebih murah" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                                </div>
                            @endif

                            @if ($stage === 'won')
                                <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-3">
                                    @if ($promptingWonReason)
                                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 mb-2">
                                            Mau di-close WON — isi alasan menangnya dulu, baru bisa disimpan & beneran pindah stage.
                                        </p>
                                    @endif
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Kategori Alasan Menang</label>
                                    <select wire:model="won_category" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm mb-2">
                                        <option value="">— pilih alasan —</option>
                                        @foreach ($wonCategories as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('won_category') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                                    <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Detail Tambahan (opsional)</label>
                                    <textarea wire:model="won_reason" rows="2" placeholder="cth. Menang karena demo POC berhasil meyakinkan tim IT customer" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                                </div>
                            @endif
                        </div>

                        {{-- ===== TAB: Tim ===== --}}
                        <div x-show="tab === 'tim'" x-cloak>
                            <div class="{{ in_array($stage, ['develop', 'won'], true) ? 'grid grid-cols-1 sm:grid-cols-2 gap-3' : '' }} mb-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Sales (assigned) <span class="text-rose-500">*</span></label>
                                    <select wire:model="sales_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                        <option value="">— pilih sales —</option>
                                        @foreach ($salesOptions as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sales_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Presales baru relevan begitu opty masuk Develop, wajib pas WON.
                                     Disembunyiin total di Leads/Lost biar form-nya ringkes. --}}
                                @if (in_array($stage, ['develop', 'won'], true))
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">
                                            Presales / Tim Produk
                                            @if ($stage === 'won')<span class="text-rose-500">*</span>@endif
                                        </label>
                                        <select wire:model="presales_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                            <option value="">— pilih presales —</option>
                                            @foreach ($presalesOptions as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('presales_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                            </div>

                            {{-- Tim Engineer cuma relevan pas opty udah/mau Close WIN — disembunyiin
                                 total di stage lain, bukan cuma ditandain "opsional". --}}
                            @if ($stage === 'won')
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">
                                        Estimasi Tim Engineer <span class="text-rose-500">* wajib diisi minimal 1</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @forelse ($engineerOptions as $e)
                                            <label class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm cursor-pointer has-[:checked]:border-sky has-[:checked]:bg-sky/5 has-[:checked]:text-sky transition">
                                                <input type="checkbox" wire:model="engineer_ids" value="{{ $e->id }}" class="rounded border-gray-300 text-sky focus:ring-sky shrink-0">
                                                <span class="truncate">{{ $e->name }}</span>
                                            </label>
                                        @empty
                                            <div class="col-span-2 text-xs text-gray-400 dark:text-gray-500 py-2">Belum ada data engineer aktif.</div>
                                        @endforelse
                                    </div>
                                    @error('engineer_ids') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @else
                                @if ($stage === 'leads')
                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic">Presales muncul begitu opty naik ke Develop, Tim Engineer muncul begitu di-close WON.</p>
                                @elseif ($stage === 'develop')
                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic">Tim Engineer muncul begitu opty ini di-close WON.</p>
                                @elseif ($stage === 'lost')
                                    <p class="text-xs text-gray-400 dark:text-gray-500 italic">Presales & Tim Engineer gak relevan lagi buat opty yang udah Lost.</p>
                                @endif
                            @endif
                        </div>

                        {{-- ===== TAB: Catatan ===== --}}
                        <div x-show="tab === 'catatan'" x-cloak>
                            {{-- Next Action sekarang checklist yang nyesuain stage (bukan teks
                                 bebas lagi) — Leads: cari harga ke Disti/Vendor. Develop: bikin
                                 Quotation ATAU PO. WON: bikin BAST ATAU Invoice. Lost gak butuh
                                 apa-apa. Progress-nya kepake buat summary "belum di-checklist"
                                 di Home. --}}
                            @php
                                $nextActionItems = \App\Models\Opportunity::NEXT_ACTION_ITEMS[$stage] ?? [];
                            @endphp
                            @if (count($nextActionItems))
                                <div class="mb-4">
                                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Next Action</label>
                                    <div class="space-y-1.5">
                                        @foreach ($nextActionItems as $key => $label)
                                            <label class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm cursor-pointer has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-500/10 transition">
                                                <input type="checkbox" wire:model="next_action_checklist.{{ $key }}" class="rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500">
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    {{-- Saran vendor yang lini produknya cocok sama Lini Produk opty ini —
                                         cuma di Leads, soalnya di situ checklist-nya emang "cari harga ke
                                         Disti/Vendor". Reaktif ngikutin pilihan Lini Produk di tab Info Utama. --}}
                                    @if ($stage === 'leads')
                                        <div class="mt-2.5 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-lg px-3 py-2.5">
                                            @if ($suggestedVendors->isNotEmpty())
                                                <div class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-300 mb-1">Vendor yang cocok buat "{{ $categories[$category] ?? '' }}":</div>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($suggestedVendors as $sv)
                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-300">{{ $sv->name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-[11px] text-indigo-600 dark:text-indigo-300">
                                                    Belum ada vendor yang ditandain buat lini "{{ $categories[$category] ?? '' }}" —
                                                    <a href="{{ route('crm.vendors') }}" class="underline font-semibold">tambahin di menu Vendor</a>.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @elseif ($stage === 'leads')
                                <p class="text-xs text-gray-400 dark:text-gray-500 italic mb-4">Next Action muncul begitu opty ini naik ke stage Develop.</p>
                            @elseif ($stage === 'lost')
                                <p class="text-xs text-gray-400 dark:text-gray-500 italic mb-4">Gak ada Next Action buat opty yang udah Lost.</p>
                            @endif
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Catatan</label>
                                <textarea wire:model="notes" rows="4" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-4 sm:px-6 py-3.5 border-t border-gray-100 dark:border-gray-700 shrink-0">
                        @if ($editingId && $canManageFull)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus opty ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-3.5 py-2 rounded-lg shrink-0">
                                Hapus
                            </button>
                        @else
                            <span></span>
                        @endif
                        <div class="flex gap-2 flex-wrap justify-end">
                            <button type="button" wire:click="closeModal" class="text-sm font-semibold px-3.5 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Batal</button>
                            @if (! $editingId)
                                <button type="button" wire:click="saveAndAddAnother" class="text-sm font-semibold px-3.5 py-2 rounded-lg border border-sky text-sky hover:bg-sky/5 whitespace-nowrap">
                                    Simpan & Tambah Lagi
                                </button>
                            @endif
                            <button type="submit" class="text-sm font-semibold px-3.5 py-2 rounded-lg bg-ink text-white">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    // Fix bug edit/drag di PWA & HP: draggable="true" yang statis dari server
    // bikin sebagian browser (terutama mode PWA standalone) bingung bedain
    // tap vs drag, jadi wire:click gak pernah kepencet. Solusinya: draggable
    // cuma diaktifin buat device yang beneran punya mouse (pointer: fine).
    function applyDraggable() {
        const isMouse = window.matchMedia('(pointer: fine)').matches;
        document.querySelectorAll('.opty-card').forEach((el) => {
            if (isMouse) {
                el.setAttribute('draggable', 'true');
            } else {
                el.removeAttribute('draggable');
            }
        });
    }
    applyDraggable();
    document.addEventListener('livewire:navigated', applyDraggable);
    document.addEventListener('livewire:init', () => {
        Livewire.on('board-updated', () => applyDraggable());
    });
</script>
