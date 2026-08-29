<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">CRM · Customer Insight</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Analisa Customer</h1>
        </div>
        @if ($canManage)
            <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                + Customer Baru
            </button>
        @else
            <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">Mode lihat aja</span>
        @endif
    </div>

    {{-- Search selalu keliatan, filter lain dipisah jadi panel collapsible
         (konsisten sama pola di Board & Report) biar gak numpuk di HP. --}}
    @php
        $activeFilterCount = collect([$date_from, $date_to, $focusOnly ?: null, $sortBy !== 'total_won' ? $sortBy : null])
            ->filter(fn ($v) => ! is_null($v) && $v !== '')
            ->count();
    @endphp
    <div class="mb-3">
        <div class="flex items-center gap-2">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama customer..." class="flex-1 min-w-0 border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2.5 text-sm">
            <button wire:click="$toggle('showFilters')" class="relative shrink-0 inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showFilters ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <x-icon name="sliders" class="w-4 h-4" />
                <span class="hidden sm:inline">Filter</span>
                @if ($activeFilterCount)
                    <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
                @endif
            </button>
        </div>

        @if ($showFilters)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mt-2 grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="date_from" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="date_to" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Urutkan</label>
                    <select wire:model.live="sortBy" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="total_won">Total WON tertinggi</option>
                        <option value="total_tcv">Total TCV tertinggi</option>
                        <option value="opty_count">Jumlah opty terbanyak</option>
                        <option value="name">Nama A-Z</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 pb-2.5">
                    <input type="checkbox" wire:model.live="focusOnly" class="rounded border-gray-300 dark:border-gray-600">
                    Fokus customer aja
                </label>

                @if ($activeFilterCount)
                    <div class="col-span-2 md:col-span-4">
                        <button wire:click="resetListFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                            <x-icon name="x" class="w-3 h-3" /> Reset semua filter
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-2">
        Tandai customer sebagai <b>Fokus</b> buat nge-highlight mana yang perlu difokusin strategi marketing —
        biasanya customer dengan total WON tinggi & transaksi rutin di periode yang lo pilih.
    </p>

    {{-- Tabel di tablet/desktop --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Customer</th>
                    <th class="p-3">Jumlah Opty</th>
                    <th class="p-3">Total TCV</th>
                    <th class="p-3">Total WON</th>
                    <th class="p-3">Transaksi WON Terakhir</th>
                    <th class="p-3">Fokus</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr class="border-b border-gray-50 dark:border-gray-700/60">
                        <td class="p-3">
                            <button wire:click="openDetail({{ $c->id }})" class="text-left hover:text-sky">
                                <div class="font-semibold hover:underline">{{ $c->name }}</div>
                                @if ($c->industry)
                                    <div class="text-xs text-gray-400 dark:text-gray-500">{{ $c->industry }}</div>
                                @endif
                            </button>
                        </td>
                        <td class="p-3 font-mono">{{ $c->opportunities_count }}</td>
                        <td class="p-3 font-mono">Rp {{ number_format($c->total_tcv ?? 0, 0, ',', '.') }}</td>
                        <td class="p-3 font-mono font-semibold text-emerald-600">Rp {{ number_format($c->total_won ?? 0, 0, ',', '.') }}</td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">{{ $c->last_won_at ? \Carbon\Carbon::parse($c->last_won_at)->translatedFormat('d M Y') : '—' }}</td>
                        <td class="p-3">
                            <button @if ($canManage) wire:click="toggleFocus({{ $c->id }})" @else disabled @endif @class([
                                'text-[11px] font-semibold px-2.5 py-1 rounded-full',
                                'bg-amber-50 text-amber-600' => $c->is_focus,
                                'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' => ! $c->is_focus,
                                'cursor-not-allowed opacity-60' => ! $canManage,
                            ])>
                                {{ $c->is_focus ? '★ Fokus' : 'Tandai' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <button wire:click="openDetail({{ $c->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:text-white">Detail</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada customer{{ $canManage ? '. Klik "+ Customer Baru" buat mulai.' : '.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile — 7 kolom gak bakal muat rapi tanpa scroll
         horizontal yang bikin nama customer kepotong. --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($customers as $c)
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <button wire:click="openDetail({{ $c->id }})" class="min-w-0 text-left hover:text-sky">
                        <div class="font-semibold text-sm truncate hover:underline">{{ $c->name }}</div>
                        @if ($c->industry)
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $c->industry }}</div>
                        @endif
                    </button>
                    <button @if ($canManage) wire:click="toggleFocus({{ $c->id }})" @else disabled @endif @class([
                        'text-[11px] font-semibold px-2.5 py-1 rounded-full shrink-0',
                        'bg-amber-50 text-amber-600' => $c->is_focus,
                        'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500' => ! $c->is_focus,
                        'cursor-not-allowed opacity-60' => ! $canManage,
                    ])>
                        {{ $c->is_focus ? '★ Fokus' : 'Tandai' }}
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                    <div>
                        <div class="text-gray-400 dark:text-gray-500">Jumlah Opty</div>
                        <div class="font-mono font-semibold">{{ $c->opportunities_count }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 dark:text-gray-500">Total TCV</div>
                        <div class="font-mono font-semibold">Rp {{ number_format($c->total_tcv ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 dark:text-gray-500">Total WON</div>
                        <div class="font-mono font-semibold text-emerald-600">Rp {{ number_format($c->total_won ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 dark:text-gray-500">WON Terakhir</div>
                        <div class="font-medium">{{ $c->last_won_at ? \Carbon\Carbon::parse($c->last_won_at)->translatedFormat('d M Y') : '—' }}</div>
                    </div>
                </div>
                <button wire:click="openDetail({{ $c->id }})" class="text-xs font-semibold text-sky">Lihat Detail →</button>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada customer{{ $canManage ? '. Klik "+ Customer Baru" buat mulai.' : '.' }}</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $customers->links() }}
    </div>

    {{-- Modal View Detail (read-only) — muncul pas klik nama, Edit-nya
         dipindah jadi tombol DI DALEM sini, bukan lagi tombol lepas. --}}
    @if ($showDetailModal && $detailCustomer)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-display font-extrabold text-lg">{{ $detailCustomer->name }}</h2>
                        @if ($detailCustomer->industry)
                            <div class="text-xs text-gray-400 dark:text-gray-500">{{ $detailCustomer->industry }}</div>
                        @endif
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">Jumlah Opty</div>
                        <div class="font-mono font-bold text-lg">{{ $detailCustomer->opportunities_count }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">Total TCV</div>
                        <div class="font-mono font-bold text-sm">Rp {{ number_format($detailCustomer->total_tcv ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">Total WON</div>
                        <div class="font-mono font-bold text-sm text-emerald-600">Rp {{ number_format($detailCustomer->total_won ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-3">
                        <div class="text-xs text-gray-400 dark:text-gray-500">WON Terakhir</div>
                        <div class="font-semibold text-sm">{{ $detailCustomer->last_won_at ? \Carbon\Carbon::parse($detailCustomer->last_won_at)->translatedFormat('d M Y') : '—' }}</div>
                    </div>
                </div>

                <div class="space-y-3 text-sm mb-2">
                    @if ($detailCustomer->is_focus)
                        <div><span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-600">★ Fokus</span></div>
                    @endif
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Nama PIC</div>
                        <div>{{ $detailCustomer->pic_name ?: '—' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">No. HP PIC</div>
                            <div>{{ $detailCustomer->pic_phone ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Email PIC</div>
                            <div class="truncate">{{ $detailCustomer->pic_email ?: '—' }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Alamat</div>
                        <div>{{ $detailCustomer->address ?: '—' }}</div>
                    </div>
                    @if ($detailCustomer->notes)
                        <div>
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Catatan</div>
                            <div>{{ $detailCustomer->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button wire:click="closeDetail" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Tutup</button>
                    @if ($canManage)
                        <button wire:click="editFromDetail({{ $detailCustomer->id }})" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Edit</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Form (create/edit) --}}
    @if ($showModal && $canManage)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Customer' : 'Customer Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Customer <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Industri</label>
                            <input type="text" wire:model="industry" placeholder="cth. Perbankan" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama PIC</label>
                            <input type="text" wire:model="pic_name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">No. HP PIC</label>
                            <input type="text" wire:model="pic_phone" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Email PIC</label>
                            <input type="email" wire:model="pic_email" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                            @error('pic_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Alamat <span class="text-rose-500">*</span></label>
                        <textarea wire:model="address" rows="3" placeholder="Alamat lengkap customer" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                        @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus customer ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
                                Hapus
                            </button>
                        @else
                            <span></span>
                        @endif
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeModal" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Batal</button>
                            <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
