<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">Super Admin</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Kelola Akun</h1>
        </div>
        <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
            + Akun Baru
        </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
            <thead>
                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                    <th class="p-3">Nama</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    <tr class="border-b border-gray-50">
                        <td class="p-3 font-semibold">
                            {{ $u->name }}
                            @if ($u->id === auth()->id())
                                <span class="text-[10px] text-gray-400 font-normal">(lo)</span>
                            @endif
                        </td>
                        <td class="p-3 text-gray-500">{{ $u->email }}</td>
                        <td class="p-3">
                            @if ($u->is_admin)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber">Super Admin</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Member</span>
                            @endif
                        </td>
                        <td class="p-3 text-right space-x-3">
                            <button wire:click="resetPassword({{ $u->id }})" wire:confirm="Reset password {{ $u->name }}? Password lama bakal langsung gak berlaku." class="text-xs font-semibold text-gray-500 hover:text-ink">Reset Password</button>
                            <button wire:click="openEdit({{ $u->id }})" class="text-xs font-semibold text-gray-500 hover:text-ink">Edit</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-md max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">

                @if ($revealedPassword)
                    <div class="mb-4">
                        <h2 class="font-display font-extrabold text-lg mb-3">Password baru dibuat</h2>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <div class="text-xs text-gray-500 mb-1">{{ $revealedFor }}</div>
                            <div class="font-mono font-bold text-lg text-emerald-700 select-all">{{ $revealedPassword }}</div>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2">
                            Copy sekarang juga — ini cuma ditampilin sekali. Sebar ke orangnya lewat WA personal, bukan grup.
                        </p>
                        <button wire:click="closeModal" class="w-full mt-4 bg-ink text-white font-semibold text-sm py-2.5 rounded-lg">
                            Udah Dicatat, Tutup
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Akun' : 'Akun Baru' }}</h2>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
                    </div>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Nama</label>
                            <input type="text" wire:model="name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="is_admin" class="rounded border-gray-300">
                            Super Admin (bisa kelola akun & password orang lain)
                        </label>
                        @error('is_admin') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                        @if (! $editingId)
                            <p class="text-[11px] text-gray-400">Password bakal di-generate otomatis dan ditampilin sekali setelah disimpan.</p>
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
                                <button type="button" wire:click="closeModal" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 text-gray-600">Batal</button>
                                <button type="submit" class="text-sm font-semibold px-4 py-2 rounded-lg bg-ink text-white">Simpan</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
