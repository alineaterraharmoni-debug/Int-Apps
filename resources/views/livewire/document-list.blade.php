<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Dokumen</h1>
        </div>
    </div>

    @if (session('deleted'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-lg px-3 py-2.5 mb-4">
            Dokumen berhasil dihapus.
        </div>
    @endif

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

    {{-- Tab jenis dokumen — "kerasa" kepisah kayak dashboard sendiri-sendiri,
         padahal masih 1 tabel/1 halaman di baliknya. Dipilih ketimbang bikin
         4 menu terpisah, soalnya dokumen-dokumen ini saling ke-link satu sama
         lain (nomor referensi Quotation->PO->Invoice), jadi lebih kepake
         kalau tetep bisa diliat dalam satu alur/timeline yang sama. --}}
    <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-sm mb-4 flex-wrap">
        <button wire:click="$set('typeFilter', '')" class="px-3 py-1.5 rounded-md font-medium whitespace-nowrap {{ $typeFilter === '' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">Semua</button>
        @foreach ($types as $val => $label)
            <button wire:click="$set('typeFilter', '{{ $val }}')" class="px-3 py-1.5 rounded-md font-medium whitespace-nowrap {{ $typeFilter === $val ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">{{ $val === 'quotation' ? 'Quotation' : ($val === 'po' ? 'PO' : $label) }}</button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="sm:col-span-2">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Cari nomor / nama customer / vendor</label>
            <input type="text" wire:model.live.debounce.400ms="search" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Status</label>
            <select wire:model.live="statusFilter" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                <option value="">Semua</option>
                @foreach ($statuses as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabel di tablet/desktop --}}
    <div class="hidden md:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-3">Nomor</th>
                    <th class="p-3">Jenis</th>
                    <th class="p-3">Status</th>
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
                        <td class="p-3">
                            @if ($doc->status === 'draft')
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Draft</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Final</span>
                            @endif
                        </td>
                        <td class="p-3 text-gray-500 dark:text-gray-400">{{ $doc->doc_date->translatedFormat('d M Y') }}</td>
                        <td class="p-3">{{ $doc->recipient_name ?? '—' }}</td>
                        <td class="p-3 font-mono">Rp {{ number_format($doc->total, 0, ',', '.') }}</td>
                        <td class="p-3 text-right space-x-3 whitespace-nowrap">
                            <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white">PDF</a>
                            @if ($canManage)
                                <a href="{{ route('documents.edit', $doc->id) }}" class="text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-ink dark:hover:text-white">Edit</a>
                                <button wire:click="delete({{ $doc->id }})" wire:confirm="Yakin mau hapus dokumen {{ $doc->number }}? Kalau ini nge-link ke opty, checklist Next Action terkait ikut ke-uncheck otomatis." class="text-xs font-semibold text-red-500 hover:text-red-600">Hapus</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada dokumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card list di mobile — 7 kolom gak bakal muat rapi tanpa scroll horizontal. --}}
    <div class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl divide-y-2 divide-gray-100 dark:divide-gray-700 overflow-hidden">
        @forelse ($documents as $doc)
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <div class="font-mono font-semibold text-sm">{{ $doc->number }}</div>
                    @if ($doc->status === 'draft')
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 shrink-0">Draft</span>
                    @else
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0">Final</span>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 mb-2">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $doc->type_label }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $doc->doc_date->translatedFormat('d M Y') }}</span>
                </div>
                <div class="text-sm mb-1">{{ $doc->recipient_name ?? '—' }}</div>
                <div class="font-mono font-semibold text-sm mb-3">Rp {{ number_format($doc->total, 0, ',', '.') }}</div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank" class="text-xs font-semibold text-gray-600 dark:text-gray-300">PDF</a>
                    @if ($canManage)
                        <a href="{{ route('documents.edit', $doc->id) }}" class="text-xs font-semibold text-sky">Edit</a>
                        <button wire:click="delete({{ $doc->id }})" wire:confirm="Yakin mau hapus dokumen {{ $doc->number }}? Kalau ini nge-link ke opty, checklist Next Action terkait ikut ke-uncheck otomatis." class="text-xs font-semibold text-red-500">Hapus</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada dokumen.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $documents->links() }}
    </div>
</div>
