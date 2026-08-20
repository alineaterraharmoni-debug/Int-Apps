<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">CRM · Tim</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Sales, Presales & Engineer</h1>
        </div>
        <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
            + Tambah Orang
        </button>
    </div>

    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-4">
        Orang di sini yang muncul di dropdown assignment Sales/Presales/Engineer pas bikin opty. Satu orang bisa punya lebih dari satu peran.
    </p>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm min-w-[600px]">
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
                            <div class="flex gap-1 flex-wrap">
                                @foreach ($m->roles ?? [] as $role)
                                    <span @class([
                                        'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                        'bg-sky-50 text-sky' => $role === 'sales',
                                        'bg-teal-50 text-teal' => $role === 'presales',
                                        'bg-amber-50 text-amber' => $role === 'engineer',
                                    ])>{{ ucfirst($role) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="p-3">
                            @if ($m->is_active)
                                <span class="text-[11px] font-semibold text-emerald-600">Aktif</span>
                            @else
                                <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono text-gray-500 dark:text-gray-400">
                            {{ $m->opportunities_as_sales_count + $m->opportunities_as_presales_count + $m->opportunities_as_engineer_count }}
                        </td>
                        <td class="p-3 text-right">
                            <button wire:click="openEdit({{ $m->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:text-white">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada orang. Klik "+ Tambah Orang" buat mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
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
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 dark:border-gray-600">
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
