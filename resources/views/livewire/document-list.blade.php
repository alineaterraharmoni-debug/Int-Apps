<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Dokumen</h1>
        </div>
    </div>

    @if (session('deleted'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-lg px-3 py-2.5 mb-4">
            Dokumen berhasil dihapus.
        </div>
    @endif

    @if ($canManage)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
            <a href="{{ route('documents.create', 'quotation') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">📄</div>
                <div class="text-xs font-semibold">+ Quotation</div>
            </a>
            <a href="{{ route('documents.create', 'invoice') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">🧾</div>
                <div class="text-xs font-semibold">+ Invoice</div>
            </a>
            <a href="{{ route('documents.create', 'po') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">📦</div>
                <div class="text-xs font-semibold">+ Purchase Order</div>
            </a>
            <a href="{{ route('documents.create', 'bast') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">✅</div>
                <div class="text-xs font-semibold">+ BAST</div>
            </a>
        </div>
    @endif

    {{-- Tab jenis dokumen — "kerasa" kepisah kayak dashboard sendiri-sendiri,
         padahal masih 1 tabel/1 halaman di baliknya. --}}
    <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-sm mb-4 flex-wrap">
        <button wire:click="$set('typeFilter', '')" class="px-3 py-1.5 rounded-md font-medium whitespace-nowrap {{ $typeFilter === '' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">Semua</button>
        @foreach ($types as $val => $label)
            <button wire:click="$set('typeFilter', '{{ $val }}')" class="px-3 py-1.5 rounded-md font-medium whitespace-nowrap {{ $typeFilter === $val ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $val === 'quotation' ? 'Quotation' : ($val === 'po' ? 'PO' : $label) }}</button>
        @endforeach
    </div>

    <div class="flex items-center gap-2 mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nomor / nama customer / vendor..." class="flex-1 min-w-0 border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2.5 text-sm">
        @php $activeFilterCount = collect([$statusFilter, $dateFrom, $dateTo])->filter()->count(); @endphp
        <button wire:click="$toggle('showFilters')" class="relative shrink-0 inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showFilters ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
            <x-icon name="sliders" class="w-4 h-4" />
            <span class="hidden sm:inline">Filter</span>
            @if ($activeFilterCount)
                <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
            @endif
        </button>
    </div>

    @if ($showFilters)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-4 grid grid-cols-2 md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Status</label>
                <select wire:model.live="statusFilter" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($statuses as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($activeFilterCount)
                <div class="col-span-2 md:col-span-3">
                    <button wire:click="resetListFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                        <x-icon name="x" class="w-3 h-3" /> Reset filter
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- Tabel di tablet/desktop — klik di mana aja di barisnya (SELAIN tombol
         PDF/Hapus) buka popup View Detail. Edit dipindah ke dalem popup itu. --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nomor</th>
                    <th class="p-3">Jenis</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Ditujukan ke</th>
                    <th class="p-3">Total</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $doc)
                    <tr wire:click="openDetail({{ $doc->id }})" wire:key="doc-{{ $doc->id }}" class="border-b border-gray-50 dark:border-gray-700/60 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="p-3 font-mono font-semibold">{{ $doc->number ?: '—' }}</td>
                        <td class="p-3">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $doc->type_label }}</span>
                        </td>
                        <td class="p-3">
                            @if ($doc->status === 'draft')
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Draft</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Final</span>
                            @endif
                        </td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">{{ $doc->doc_date->translatedFormat('d M Y') }}</td>
                        <td class="p-3">{{ $doc->recipient_name ?? '—' }}</td>
                        <td class="p-3 font-mono">Rp {{ number_format($doc->type === 'invoice' ? $doc->grand_total : $doc->total, 0, ',', '.') }}</td>
                        <td class="p-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank" onclick="event.stopPropagation()" class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50">PDF</a>
                                @if ($canManage)
                                    <button wire:click.stop="delete({{ $doc->id }})" wire:confirm="Yakin mau hapus dokumen {{ $doc->number ?? 'draft ini' }}? Kalau ini nge-link ke opty, checklist Next Action terkait ikut ke-uncheck otomatis." class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-rose-200 dark:border-rose-500/30 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">Hapus</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada dokumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile — 7 kolom gak bakal muat rapi tanpa scroll horizontal. --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($documents as $doc)
            <div wire:click="openDetail({{ $doc->id }})" wire:key="doc-mobile-{{ $doc->id }}" class="p-3.5 cursor-pointer active:bg-gray-50 dark:active:bg-gray-700/40">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <div class="font-mono font-semibold text-sm">{{ $doc->number ?: '—' }}</div>
                    @if ($doc->status === 'draft')
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 shrink-0">Draft</span>
                    @else
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0">Final</span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mb-2">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $doc->type_label }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $doc->doc_date->translatedFormat('d M Y') }}</span>
                </div>
                <div class="text-sm mb-1">{{ $doc->recipient_name ?? '—' }}</div>
                <div class="font-mono font-semibold text-sm mb-3">Rp {{ number_format($doc->type === 'invoice' ? $doc->grand_total : $doc->total, 0, ',', '.') }}</div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank" onclick="event.stopPropagation()" class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">PDF</a>
                    @if ($canManage)
                        <button wire:click.stop="delete({{ $doc->id }})" wire:confirm="Yakin mau hapus dokumen {{ $doc->number ?? 'draft ini' }}? Kalau ini nge-link ke opty, checklist Next Action terkait ikut ke-uncheck otomatis." class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-rose-200 dark:border-rose-500/30 text-rose-500">Hapus</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada dokumen.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $documents->links() }}
    </div>

    {{-- Modal View Detail (read-only) — Edit dipindah jadi tombol DI DALEM sini. --}}
    @if ($showDetailModal && $detailDocument)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-start justify-between gap-2 mb-4">
                    <div>
                        <h2 class="font-display font-extrabold text-lg font-mono">{{ $detailDocument->number ?: '(Draft, belum ada nomor)' }}</h2>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $detailDocument->type_label }}</span>
                            @if ($detailDocument->status === 'draft')
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Draft</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Final</span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none shrink-0">&times;</button>
                </div>

                <div class="space-y-3 text-sm mb-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Tanggal</div>
                            <div>{{ $detailDocument->doc_date->translatedFormat('d M Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Total</div>
                            <div class="font-mono font-semibold">Rp {{ number_format($detailDocument->total, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Ditujukan ke</div>
                        <div>{{ $detailDocument->recipient_name ?? '—' }}{{ $detailDocument->contact_name ? ' (PIC: '.$detailDocument->contact_name.')' : '' }}</div>
                    </div>
                    @if ($detailDocument->opportunity)
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Opty Terkait</div>
                            <div>{{ $detailDocument->opportunity->title }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Item ({{ $detailDocument->items->count() }})</div>
                        <div class="space-y-1">
                            @foreach ($detailDocument->items as $item)
                                <div class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-900/40 rounded-lg px-2.5 py-1.5">
                                    <span class="truncate pr-2">{{ \Illuminate\Support\Str::of($item->description)->explode("\n")->first() }}</span>
                                    <span class="font-mono shrink-0">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="closeDetail" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Tutup</button>
                    <a href="{{ route('documents.pdf', $detailDocument->id) }}" target="_blank" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">PDF</a>
                    @if ($canManage)
                        <a href="{{ route('documents.edit', $detailDocument->id) }}" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Edit</a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
