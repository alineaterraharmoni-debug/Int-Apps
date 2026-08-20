<div class="max-w-md mx-auto">
    <div class="mb-5">
        <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Akun Login</div>
        <h1 class="font-display font-extrabold text-xl md:text-2xl">Tambah Akun Baru</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">
        @if ($revealedPassword)
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 mb-4">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $revealedFor }}</div>
                <div class="font-mono font-bold text-lg text-emerald-700 select-all">{{ $revealedPassword }}</div>
            </div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-4">
                Copy sekarang juga — ini cuma ditampilin sekali. Sebar ke orangnya lewat WA personal, bukan grup.
            </p>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama</label>
                <input type="text" wire:model="name" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Role</label>
                <select wire:model="role_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                    <option value="">— belum ada role —</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500">Password bakal di-generate otomatis dan ditampilin sekali setelah disimpan.</p>
            <button type="submit" class="w-full bg-ink text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-gray-800">
                Buat Akun
            </button>
        </form>
    </div>
</div>
