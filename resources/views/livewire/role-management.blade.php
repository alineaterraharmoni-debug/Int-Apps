<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Super Admin</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Kelola Role</h1>
        </div>
        <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
            + Role Baru
        </button>
    </div>

    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-4">
        Role nentuin akses default yang bisa lo assign ke akun pas bikin/edit di halaman Kelola Akun.
        <b>Super Admin</b> bukan role di sini — itu toggle terpisah di Kelola Akun yang bikin akses ke SEMUANYA, gak dibatasin permission apapun.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach ($roles as $role)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="font-display font-bold text-sm">{{ $role->name }}</div>
                        <div class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ $role->users_count }} akun pakai role ini</div>
                    </div>
                    @if ($role->is_system)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber">Bawaan</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-1 mb-3">
                    @forelse ($role->permissions ?? [] as $perm)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-50 text-sky">{{ \App\Support\Permissions::label($perm) }}</span>
                    @empty
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">Belum ada akses di-centang</span>
                    @endforelse
                </div>
                @unless ($role->is_system)
                    <button wire:click="openEdit({{ $role->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:text-white">Edit</button>
                @endunless
                <button wire:click="duplicateRole({{ $role->id }})" class="text-xs font-semibold text-sky {{ $role->is_system ? '' : 'ml-3' }}">Duplicate</button>
            </div>
        @endforeach
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Role' : 'Role Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Role</label>
                        <input type="text" wire:model="name" placeholder="cth. Sales Junior" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @foreach ($catalog as $group => $permissions)
                        @php
                            $groupKeys = array_keys($permissions);
                            $allChecked = count(array_intersect($groupKeys, $selectedPermissions)) === count($groupKeys);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-xs font-bold text-gray-600 dark:text-gray-300">{{ $group }}</div>
                                <button type="button" wire:click="toggleGroup('{{ $group }}')" class="text-[11px] font-semibold text-sky">
                                    {{ $allChecked ? 'Uncheck semua' : 'Centang semua' }}
                                </button>
                            </div>
                            <div class="space-y-1.5 pl-1">
                                @foreach ($permissions as $key => $label)
                                    <label class="flex items-start gap-2 text-sm">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $key }}" class="rounded border-gray-300 dark:border-gray-600 mt-0.5">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <p class="text-[11px] text-gray-400 dark:text-gray-500">
                        Catatan: kalau centang "opty penuh semua stage", opsi "cuma sampai Leads" otomatis diabaikan (gak bisa dua-duanya).
                    </p>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus role ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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
