<div class="max-w-md mx-auto">
    <div class="mb-5">
        <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Akun Saya</div>
        <h1 class="font-display font-extrabold text-xl md:text-2xl">Ganti Password</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5">
        @if ($success)
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-lg px-3 py-2.5 mb-4">
                Password berhasil diganti.
            </div>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Password Sekarang</label>
                <input type="password" wire:model="current_password" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Password Baru</label>
                <input type="password" wire:model="new_password" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Minimal 8 karakter.</p>
                @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Ulangi Password Baru</label>
                <input type="password" wire:model="new_password_confirmation" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-ink text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-gray-800">
                Simpan Password Baru
            </button>
        </form>
    </div>
</div>
