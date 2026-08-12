<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">CRM · Customer Insight</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Analisa Customer</h1>
        </div>
        <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
            + Customer Baru
        </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-5 grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
        <div class="col-span-2 md:col-span-1">
            <label class="text-xs font-semibold text-gray-500 block mb-1">Cari nama</label>
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="cth. Bank XYZ" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 block mb-1">Dari Tanggal</label>
            <input type="date" wire:model.live="date_from" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 block mb-1">Sampai Tanggal</label>
            <input type="date" wire:model.live="date_to" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 block mb-1">Urutkan</label>
            <select wire:model.live="sortBy" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
                <option value="total_won">Total WON tertinggi</option>
                <option value="total_tcv">Total TCV tertinggi</option>
                <option value="opty_count">Jumlah opty terbanyak</option>
                <option value="name">Nama A-Z</option>
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600 pb-2">
            <input type="checkbox" wire:model.live="focusOnly" class="rounded border-gray-300">
            Fokus customer aja
        </label>
    </div>

    <p class="text-[11px] text-gray-400 mb-2">
        Tandai customer sebagai <b>Fokus</b> buat nge-highlight mana yang perlu difokusin strategi marketing —
        biasanya customer dengan total WON tinggi & transaksi rutin di periode yang lo pilih.
    </p>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm min-w-[760px] md:min-w-0">
            <thead>
                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
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
                    <tr class="border-b border-gray-50">
                        <td class="p-3">
                            <div class="font-semibold">{{ $c->name }}</div>
                            @if ($c->industry)
                                <div class="text-xs text-gray-400">{{ $c->industry }}</div>
                            @endif
                        </td>
                        <td class="p-3 font-mono">{{ $c->opportunities_count }}</td>
                        <td class="p-3 font-mono">Rp {{ number_format($c->total_tcv ?? 0, 0, ',', '.') }}</td>
                        <td class="p-3 font-mono font-semibold text-emerald-600">Rp {{ number_format($c->total_won ?? 0, 0, ',', '.') }}</td>
                        <td class="p-3 text-gray-500">{{ $c->last_won_at ? \Carbon\Carbon::parse($c->last_won_at)->translatedFormat('d M Y') : '—' }}</td>
                        <td class="p-3">
                            <button wire:click="toggleFocus({{ $c->id }})" @class([
                                'text-[11px] font-semibold px-2.5 py-1 rounded-full',
                                'bg-amber-50 text-amber-600' => $c->is_focus,
                                'bg-gray-100 text-gray-400' => ! $c->is_focus,
                            ])>
                                {{ $c->is_focus ? '★ Fokus' : 'Tandai' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <button wire:click="openEdit({{ $c->id }})" class="text-xs font-semibold text-gray-500 hover:text-ink">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-400">Belum ada customer. Klik "+ Customer Baru" buat mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Customer' : 'Customer Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Nama Customer</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Industri</label>
                            <input type="text" wire:model="industry" placeholder="cth. Perbankan" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Nama PIC</label>
                            <input type="text" wire:model="pic_name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">No. HP PIC</label>
                            <input type="text" wire:model="pic_phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Email PIC</label>
                            <input type="email" wire:model="pic_email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('pic_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
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
                            <button type="button" wire:click="closeModal" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 text-gray-600">Batal</button>
                            <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
