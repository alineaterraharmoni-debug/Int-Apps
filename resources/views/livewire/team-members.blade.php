<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">CRM · Tim</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Sales, Presales & Engineer</h1>
        </div>
        @if ($canManage)
            <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                + Tambah Orang
            </button>
        @else
            <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">Mode lihat aja</span>
        @endif
    </div>

    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-3">
        Orang di sini yang muncul di dropdown assignment Sales/Presales/Engineer pas bikin opty. Satu orang bisa punya lebih dari satu peran.
    </p>

    {{-- Search selalu keliatan, filter lain dipisah jadi panel collapsible --}}
    @php
        $activeFilterCount = collect([$filterRole, $filterStatus])->filter(fn ($v) => $v !== '')->count();
    @endphp
    <div class="mb-4">
        <div class="flex items-center gap-2">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama..." class="flex-1 min-w-0 border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2.5 text-sm">
            <button wire:click="$toggle('showFilters')" class="relative shrink-0 inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showFilters ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                <x-icon name="sliders" class="w-4 h-4" />
                <span class="hidden sm:inline">Filter</span>
                @if ($activeFilterCount)
                    <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
                @endif
            </button>
        </div>

        @if ($showFilters)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mt-2 grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Peran</label>
                    <select wire:model.live="filterRole" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="sales">Sales</option>
                        <option value="presales">Presales</option>
                        <option value="engineer">Engineer</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>

                @if ($activeFilterCount)
                    <div class="col-span-2">
                        <button wire:click="resetListFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                            <x-icon name="x" class="w-3 h-3" /> Reset semua filter
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Tabel di tablet/desktop --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nama</th>
                    <th class="p-3">Peran</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Opty Terkait</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $m)
                    <tr class="border-b border-gray-50 dark:border-gray-700/60">
                        <td class="p-3 font-semibold">{{ $m->name }}</td>
                        <td class="p-3">
                            @if (empty($m->roles))
                                <span class="text-[11px] text-gray-400 dark:text-gray-500 italic">Belum ada peran</span>
                            @else
                                <div class="flex gap-1 flex-wrap">
                                    @foreach ($m->roles as $role)
                                        <span @class([
                                            'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                            'bg-sky-50 text-sky' => $role === 'sales',
                                            'bg-teal-50 text-teal' => $role === 'presales',
                                            'bg-amber-50 text-amber' => $role === 'engineer',
                                        ])>{{ ucfirst($role) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="p-3">
                            @if ($m->is_active)
                                <span class="text-[11px] font-semibold text-emerald-600">Aktif</span>
                            @else
                                <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if ($m->hasRole('sales') || $m->hasRole('presales') || $m->hasRole('engineer'))
                                <div class="flex flex-col gap-0.5 font-mono text-xs text-gray-500 dark:text-gray-400">
                                    @if ($m->hasRole('sales'))<span>Sales: {{ $m->opportunities_as_sales_count }}</span>@endif
                                    @if ($m->hasRole('presales'))<span>Presales: {{ $m->opportunities_as_presales_count }}</span>@endif
                                    @if ($m->hasRole('engineer'))<span>Engineer: {{ $m->opportunities_as_engineer_count }}</span>@endif
                                </div>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            @if ($canManage)
                                <button wire:click="openEdit({{ $m->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:text-white">Edit</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada orang{{ $canManage ? '. Klik "+ Tambah Orang" buat mulai.' : '.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($members as $m)
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="font-semibold text-sm">{{ $m->name }}</div>
                    @if ($m->is_active)
                        <span class="text-[10px] font-semibold text-emerald-600 shrink-0">Aktif</span>
                    @else
                        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 shrink-0">Nonaktif</span>
                    @endif
                </div>

                @if (empty($m->roles))
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 italic">Belum ada peran</span>
                @else
                    <div class="flex gap-1 flex-wrap mb-2">
                        @foreach ($m->roles as $role)
                            <span @class([
                                'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                'bg-sky-50 text-sky' => $role === 'sales',
                                'bg-teal-50 text-teal' => $role === 'presales',
                                'bg-amber-50 text-amber' => $role === 'engineer',
                            ])>{{ ucfirst($role) }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($m->hasRole('sales') || $m->hasRole('presales') || $m->hasRole('engineer'))
                    <div class="flex flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-gray-500 dark:text-gray-400 mb-2">
                        @if ($m->hasRole('sales'))<span>Sales: {{ $m->opportunities_as_sales_count }}</span>@endif
                        @if ($m->hasRole('presales'))<span>Presales: {{ $m->opportunities_as_presales_count }}</span>@endif
                        @if ($m->hasRole('engineer'))<span>Engineer: {{ $m->opportunities_as_engineer_count }}</span>@endif
                    </div>
                @endif

                @if ($canManage)
                    <button wire:click="openEdit({{ $m->id }})" class="text-xs font-semibold text-sky">Edit →</button>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada orang{{ $canManage ? '. Klik "+ Tambah Orang" buat mulai.' : '.' }}</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $members->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal && $canManage)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-md max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Orang' : 'Tambah Orang' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-2">Peran (bisa lebih dari satu)</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" wire:model="roles" value="sales" class="rounded border-gray-300 dark:border-gray-600"> Sales
                            </label>
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" wire:model="roles" value="presales" class="rounded border-gray-300 dark:border-gray-600"> Presales
                            </label>
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" wire:model="roles" value="engineer" class="rounded border-gray-300 dark:border-gray-600"> Engineer
                            </label>
                        </div>
                        @error('roles') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($editingId && ! $is_active)
                        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-3 py-2.5 text-xs text-amber-800 dark:text-amber-300">
                            Orang ini bakal ilang dari dropdown assignment Sales/Presales/Engineer selama nonaktif — opty yang UDAH ke-assign ke dia gak kepengaruh, cuma gak bisa di-assign opty BARU.
                        </div>
                    @endif
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" wire:model.live="is_active" class="rounded border-gray-300 dark:border-gray-600">
                        Aktif (muncul di dropdown assignment)
                    </label>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus orang ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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
