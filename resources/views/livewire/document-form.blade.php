<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Dokumen</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">{{ $editingId ? 'Edit' : 'Buat' }} {{ $types[$type] }}</h1>
            <div class="text-xs font-mono text-gray-400 dark:text-gray-500 mt-1">Nomor: {{ $previewNumber }}</div>
        </div>
        @if ($editingId)
            <a href="{{ route('documents.pdf', $editingId) }}" target="_blank" class="bg-ink text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-gray-800">
                Download PDF
            </a>
        @endif
    </div>

    @if (session('saved'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-lg px-3 py-2.5 mb-4">
            Dokumen {{ session('saved') }} berhasil disimpan.
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        {{-- Info dasar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Tanggal</label>
                    <input type="date" wire:model="doc_date" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    @error('doc_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Link ke Opty (opsional)</label>
                    <select wire:model="opportunity_id" wire:change="selectOpportunity" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                        <option value="">— gak dilink —</option>
                        @foreach ($opportunities as $o)
                            <option value="{{ $o->id }}">{{ $o->title }} ({{ $o->customer?->name }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($type === 'po')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Vendor / Distributor</label>
                        <select wire:model="vendor_id" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <option value="">— pilih vendor —</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Contact Person Vendor</label>
                        <input type="text" wire:model="contact_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Customer</label>
                        <select wire:model="customer_id" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <option value="">— pilih customer —</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Contact Person Customer</label>
                        <input type="text" wire:model="contact_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            @endif

            @if (in_array($type, ['invoice', 'bast']))
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">No. Quotation Referensi</label>
                        <input type="text" wire:model="ref_quotation_number" placeholder="cth. 006-QUO/AD/05/26" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">No. PO Referensi</label>
                        <input type="text" wire:model="ref_po_number" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    </div>
                    @if ($type === 'bast')
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">No. Invoice Referensi</label>
                            <input type="text" wire:model="ref_invoice_number" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Item baris dinamis --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-display font-bold text-sm">Item</div>
                <button type="button" wire:click="addItem" class="text-xs font-semibold text-sky">+ Tambah Baris</button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $i => $item)
                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-3 relative">
                        <button type="button" wire:click="removeItem({{ $i }})" class="absolute top-2 right-2 text-gray-300 dark:text-gray-600 hover:text-red-500 text-lg leading-none">&times;</button>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                            <input type="text" wire:model="items.{{ $i }}.group_label" placeholder="Label grup (opsional, cth. Option 1 - TrendAI Essentials)" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-xs">
                            @if ($type === 'po')
                                <input type="text" wire:model="items.{{ $i }}.product_type" placeholder="Product Type (cth. TrendAI Flex (credits))" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-xs">
                            @endif
                        </div>

                        <textarea wire:model="items.{{ $i }}.description" rows="2" placeholder="Deskripsi item..." class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm mb-2"></textarea>
                        @error("items.{$i}.description") <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Qty</label>
                                <input type="number" step="0.01" wire:model="items.{{ $i }}.qty" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Unit</label>
                                <input type="text" wire:model="items.{{ $i }}.unit" placeholder="Lot/Unit" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Credits</label>
                                <input type="number" wire:model="items.{{ $i }}.credits_required" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Unit Price (Rp)</label>
                                <input type="number" step="1" wire:model="items.{{ $i }}.unit_price" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Amount</label>
                                <div class="text-sm font-mono font-semibold py-1.5">
                                    Rp {{ number_format((float)($item['qty'] ?: 0) * (float)($item['unit_price'] ?: 0), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="text-right">
                    <div class="text-xs text-gray-400 dark:text-gray-500">Grand Total</div>
                    <div class="font-mono font-bold text-lg">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- Terms & signatory --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Terms and Condition</label>
                <textarea wire:model="terms" rows="6" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm font-mono"></textarea>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Penandatangan</label>
                <input type="text" wire:model="signatory_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('documents.index') }}" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">Batal</a>
            <button type="submit" class="text-sm font-semibold px-5 py-2 rounded-lg bg-ink text-white">Simpan Dokumen</button>
        </div>
    </form>
</div>
