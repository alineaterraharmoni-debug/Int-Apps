<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Super Admin</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Kelola Akun</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('roles') }}" class="text-sm font-semibold px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-gray-300 dark:border-gray-600 whitespace-nowrap">
                Kelola Role
            </a>
            <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                + Akun Baru
            </button>
        </div>
    </div>

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama atau email..." class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2.5 text-sm mb-4">

    {{-- Tabel di tablet/desktop --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nama</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr class="border-b border-gray-50 dark:border-gray-700/60">
                        <td class="p-3 font-semibold">
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal">(lo)</span>
                            @endif
                        </td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">{{ $u->email }}</td>
                        <td class="p-3">
                            @if ($u->is_admin)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber">Super Admin</span>
                            @elseif ($u->role)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 text-sky">{{ $u->role->name }}</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Belum ada role</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if ($u->is_active)
                                <span class="text-[11px] font-semibold text-emerald-600">Aktif</span>
                            @else
                                <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="resetPassword({{ $u->id }})" wire:confirm="Reset password {{ $u->name }}? Password lama bakal langsung gak berlaku." class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    Reset Password
                                </button>
                                <button wire:click="openEdit({{ $u->id }})" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada akun. Klik "+ Akun Baru" buat mulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($users as $u)
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <div class="min-w-0">
                        <div class="font-semibold text-sm truncate">
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal">(lo)</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $u->email }}</div>
                    </div>
                    @if ($u->is_active)
                        <span class="text-[10px] font-semibold text-emerald-600 shrink-0">Aktif</span>
                    @else
                        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 shrink-0">Nonaktif</span>
                    @endif
                </div>
                <div class="mb-2.5">
                    @if ($u->is_admin)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber">Super Admin</span>
                    @elseif ($u->role)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 text-sky">{{ $u->role->name }}</span>
                    @else
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Belum ada role</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetPassword({{ $u->id }})" wire:confirm="Reset password {{ $u->name }}? Password lama bakal langsung gak berlaku." class="flex-1 text-xs font-semibold text-gray-600 dark:text-gray-300 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700">
                        Reset Password
                    </button>
                    <button wire:click="openEdit({{ $u->id }})" class="flex-1 text-xs font-semibold text-white bg-ink px-3 py-2 rounded-lg">
                        Edit
                    </button>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada akun. Klik "+ Akun Baru" buat mulai.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-md max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">

                @if ($revealedPassword)
                    <div class="mb-4" x-data="{ copied: false }">
                        <h2 class="font-display font-extrabold text-lg mb-3">Password baru dibuat</h2>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $revealedFor }}</div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-mono font-bold text-lg text-emerald-700 select-all">{{ $revealedPassword }}</div>
                                <button
                                    type="button"
                                    x-on:click="navigator.clipboard.writeText('{{ $revealedPassword }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="shrink-0 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-100"
                                >
                                    <span x-show="! copied">Copy</span>
                                    <span x-show="copied" x-cloak>Kecopy!</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2">
                            Copy sekarang juga — ini cuma ditampilin sekali. Sebar ke orangnya lewat WA personal, bukan grup.
                        </p>
                        <button wire:click="closeModal" class="w-full mt-4 bg-ink text-white font-semibold text-sm py-2.5 rounded-lg">
                            Udah Dicatat, Tutup
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Akun' : 'Akun Baru' }}</h2>
                        <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                    </div>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Email <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="is_admin" class="rounded border-gray-300 dark:border-gray-600">
                            Super Admin (akses ke SEMUANYA, gak dibatasin apa-apa)
                        </label>
                        @error('is_admin') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                        @unless ($is_admin)
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Role</label>
                                <select wire:model="role_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    <option value="">— belum ada role —</option>
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                    Role nentuin apa aja yang bisa diakses. Kelola pilihan role di halaman "Kelola Role".
                                </p>
                            </div>
                        @endunless

                        @if ($editingId)
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 dark:border-gray-600">
                                Aktif (bisa login)
                            </label>
                            @error('is_active') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                            @if (! $is_active)
                                <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl px-3 py-2.5 text-xs text-amber-800 dark:text-amber-300">
                                    Akun ini gak bisa login lagi selama nonaktif. Semua sesi yang lagi aktif juga langsung diputus pas disimpan.
                                </div>
                            @endif
                        @endif

                        @if (! $editingId)
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Password bakal di-generate otomatis dan ditampilin sekali setelah disimpan.</p>
                        @endif

                        <div class="flex items-center justify-between pt-2">
                            @if ($editingId)
                                <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus akun ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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
                @endif
            </div>
        </div>
    @endif
</div>
