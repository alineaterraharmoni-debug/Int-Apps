<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Dokumen</h1>
        </div>
    </div>

    @if ($canManage)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
            <a href="{{ route('documents.create', 'quotation') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">📄</div>
                <div class="text-xs font-semibold">+ Quotation</div>
            </a>
            <a href="{{ route('documents.create', 'invoice') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">🧾</div>
                <div class="text-xs font-semibold">+ Invoice</div>
            </a>
            <a href="{{ route('documents.create', 'po') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">📦</div>
                <div class="text-xs font-semibold">+ Purchase Order</div>
            </a>
            <a href="{{ route('documents.create', 'bast') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-sky rounded-xl p-3 text-center transition">
                <div class="text-2xl mb-1">✅</div>
                <div class="text-xs font-semibold">+ BAST</div>
            </a>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Cari nomor / nama customer / vendor</label>
            <input type="text" wire:model.live.debounce.400ms="search" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Jenis Dokumen</label>
            <select wire:model.live="typeFilter" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                <option value="">Semua</option>
                @foreach ($types as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nomor</th>
                    <th class="p-3">Jenis</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Ditujukan ke</th>
                    <th class="p-3">Total</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $doc)
                    <tr class="border-b border-gray-50 dark:border-gray-700/60">
                        <td class="p-3 font-mono font-semibold">{{ $doc->number }}</td>
                        <td class="p-3">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $doc->type_label }}</span>
                        </td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">{{ $doc->doc_date->translatedFormat('d M Y') }}</td>
                        <td class="p-3">{{ $doc->recipient_name ?? '—' }}</td>
                        <td class="p-3 font-mono">Rp {{ number_format($doc->total, 0, ',', '.') }}</td>
                        <td class="p-3 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white">PDF</a>
                            @if ($canManage)
                                <a href="{{ route('documents.edit', $doc->id) }}" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada dokumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
