<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">CRM · Vendor / Distributor</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Vendor</h1>
        </div>
        @if ($canManage)
            <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                + Vendor Baru
            </button>
        @else
            <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">Mode lihat aja</span>
        @endif
    </div>

    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-3">
        Lini Produk yang ditandain di sini dipake buat nyaranin vendor relevan pas checklist "Cari harga dari Disti/Vendor" (stage Leads) dan di dropdown vendor form Dokumen.
    </p>

    {{-- Search selalu keliatan, filter Lini Produk dipisah jadi panel collapsible --}}
    <div class="mb-4">
        <div class="flex items-center gap-2">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama vendor..." class="flex-1 min-w-0 border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2.5 text-sm">
            <button wire:click="$toggle('showFilters')" class="relative shrink-0 inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showFilters ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <x-icon name="sliders" class="w-4 h-4" />
                <span class="hidden sm:inline">Filter</span>
                @if ($filterType)
                    <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">1</span>
                @endif
            </button>
        </div>

        @if ($showFilters)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mt-2">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Lini Produk</label>
                <select wire:model.live="filterType" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($categories as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if ($filterType)
                    <button wire:click="resetListFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600 mt-2">
                        <x-icon name="x" class="w-3 h-3" /> Reset filter
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Tabel di tablet/desktop --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nama Vendor</th>
                    <th class="p-3">Lini Produk</th>
                    <th class="p-3">Kontak</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $v)
                    <tr class="border-b border-gray-50 dark:border-gray-700/60">
                        <td class="p-3 font-semibold">{{ $v->name }}</td>
                        <td class="p-3">
                            @forelse ($v->type ?? [] as $t)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 mr-1 mb-1 inline-block">{{ $categories[$t] ?? $t }}</span>
                            @empty
                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                            @endforelse
                        </td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">
                            @if ($v->contact_name || $v->phone)
                                {{ $v->contact_name }}{{ $v->contact_name && $v->phone ? ' · ' : '' }}{{ $v->phone }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            @if ($canManage)
                                <button wire:click="openEdit({{ $v->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:text-white">Edit</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada vendor{{ $canManage ? '. Klik "+ Vendor Baru" buat mulai.' : '.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($vendors as $v)
            <div class="p-3.5">
                <div class="font-semibold text-sm mb-1.5">{{ $v->name }}</div>
                <div class="mb-1.5">
                    @forelse ($v->type ?? [] as $t)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 mr-1 mb-1 inline-block">{{ $categories[$t] ?? $t }}</span>
                    @empty
                        <span class="text-xs text-gray-400 dark:text-gray-500">Belum ada lini produk ditandain</span>
                    @endforelse
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    @if ($v->contact_name || $v->phone)
                        {{ $v->contact_name }}{{ $v->contact_name && $v->phone ? ' · ' : '' }}{{ $v->phone }}
                    @endif
                </div>
                @if ($canManage)
                    <button wire:click="openEdit({{ $v->id }})" class="text-xs font-semibold text-sky">Edit →</button>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada vendor{{ $canManage ? '. Klik "+ Vendor Baru" buat mulai.' : '.' }}</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $vendors->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal && $canManage)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Vendor' : 'Vendor Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Vendor <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-2">
                            Lini Produk (bisa lebih dari satu)
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($categories as $key => $label)
                                <label class="flex items-center gap-2 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-2 text-sm cursor-pointer has-[:checked]:border-sky has-[:checked]:bg-sky/5 has-[:checked]:text-sky transition">
                                    <input type="checkbox" wire:model="type" value="{{ $key }}" class="rounded border-gray-300 dark:border-gray-600 text-sky focus:ring-sky shrink-0">
                                    <span class="truncate">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5">Dipake buat nyaranin vendor ini pas opty-nya se-kategori.</p>
                        @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Kontak</label>
                            <input type="text" wire:model="contact_name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">No. Telepon</label>
                            <input type="text" wire:model="phone" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Alamat</label>
                        <textarea wire:model="address" rows="3" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus vendor ini? Dokumen yang udah pernah nge-link ke vendor ini gak ikut kehapus, cuma referensi vendor-nya jadi kosong." class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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
