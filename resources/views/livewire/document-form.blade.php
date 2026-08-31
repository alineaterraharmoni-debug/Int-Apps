<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Dokumen</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">{{ $editingId ? 'Edit' : 'Buat' }} {{ $types[$type] }}</h1>
        </div>
        @if ($editingId)
            <a href="{{ route('documents.pdf', $editingId) }}" target="_blank" class="bg-ink text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-gray-800 border border-ink">
                Download PDF
            </a>
        @endif
    </div>

    @if (session('saved'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-lg px-3 py-2.5 mb-4">
            Dokumen {{ session('saved') }} berhasil disimpan.
        </div>
    @endif

    <form wire:submit="saveDraft" class="space-y-5">
        {{-- Info dasar --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Tanggal</label>
                    <input type="date" wire:model.live="doc_date" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                    @error('doc_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">
                        Nomor Dokumen
                        <span class="font-normal text-gray-400">(kosongin = auto pas Difinalisasi)</span>
                    </label>
                    <input type="text" wire:model="number" placeholder="Otomatis kalau dikosongin & di-Simpan Dokumen" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm font-mono placeholder:text-[11px]">
                    @error('number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Draft gak punya nomor sama sekali — nomor cuma kegenerate/kesimpen pas status-nya Final.</p>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">
                    Link ke Opty
                    @if ($type === 'invoice')
                        <span class="text-rose-500">*</span>
                    @else
                        <span class="font-normal text-gray-400">(opsional)</span>
                    @endif
                </label>
                <div
                    class="relative"
                    x-data="{
                        open: false,
                        q: {{ \Illuminate\Support\Js::from($opportunity_id ? optional($opportunities->firstWhere('id', $opportunity_id))->title : '') }},
                        confirmedLabel: {{ \Illuminate\Support\Js::from($opportunity_id ? optional($opportunities->firstWhere('id', $opportunity_id))->title : '') }},
                        items: {{ \Illuminate\Support\Js::from($opportunities->map(fn ($o) => ['id' => $o->id, 'title' => $o->title.' ('.($o->customer?->name ?? '-').')'])->values()) }},
                        get filtered() {
                            if (! this.q) return this.items;
                            const s = this.q.toLowerCase();
                            return this.items.filter(o => o.title.toLowerCase().includes(s));
                        },
                        // Buka dropdown DAN kosongin kotak pencarian — biar
                        // daftar LENGKAP langsung keliatan lagi (sebelumnya
                        // kotak masih keisi nama yang lama, jadi listnya
                        // ke-filter cuma nyisain yang mirip doang).
                        openList() { this.open = true; this.q = ''; },
                        pick(o) { this.confirmedLabel = o.title; this.q = o.title; this.open = false; $wire.pickOpportunity(o.id); },
                        // Ditutup TANPA milih apa-apa baru -> kotaknya balik
                        // nampilin nilai yang masih beneran ke-pilih di
                        // server, jangan dibiarin blank ngambang.
                        cancel() { this.open = false; this.q = this.confirmedLabel; },
                        clear() { this.confirmedLabel = ''; this.q = ''; this.open = false; $wire.pickOpportunity(null); }
                    }"
                    x-on:click.outside="cancel()"
                >
                    <input type="text" x-model="q"
                           x-on:focus="openList()" x-on:click="openList()"
                           x-on:input="open = true"
                           x-on:keydown.enter.prevent.stop="if (filtered.length) { pick(filtered[0]); }"
                           x-on:keydown.escape="cancel()"
                           placeholder="Cari opty... (kosongin kalau gak dilink)" autocomplete="off"
                           class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm pr-8">
                    <button type="button" x-show="confirmedLabel && ! open" x-cloak x-on:click="clear()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 dark:text-gray-600 hover:text-red-500" title="Lepas link ke opty ini" style="display:none;">
                        <x-icon name="x" class="w-3.5 h-3.5" />
                    </button>
                    <div x-show="open" x-cloak style="display:none;" class="absolute z-30 mt-1 w-full max-h-44 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                        <template x-for="o in filtered" :key="o.id">
                            <div x-on:mousedown.prevent="pick(o)" x-text="o.title" class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"></div>
                        </template>
                        <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">Gak ketemu.</div>
                    </div>
                </div>
                @error('opportunity_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @if ($type === 'invoice')
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Pilih opty biar No. Quotation/PO referensi & list item ke-isi otomatis (tetep bisa diedit manual).</p>
                @endif
            </div>

            @if ($type === 'po')
                {{-- PO tetep ditujukan ke Vendor, tapi Customer terkait (siapa yang
                     butuh barang ini) sekarang ikut disimpen juga — sebelumnya PO
                     gak nyimpen data customer sama sekali. --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Vendor / Distributor</label>
                        <div
                            class="relative"
                            x-data="{
                                open: false,
                                q: {{ \Illuminate\Support\Js::from($vendor_id ? optional($vendors->firstWhere('id', $vendor_id))->name : '') }},
                                confirmedLabel: {{ \Illuminate\Support\Js::from($vendor_id ? optional($vendors->firstWhere('id', $vendor_id))->name : '') }},
                                items: {{ \Illuminate\Support\Js::from($vendors->map(fn ($v) => ['id' => $v->id, 'name' => $v->name])->values()) }},
                                get filtered() {
                                    if (! this.q) return this.items;
                                    const s = this.q.toLowerCase();
                                    return this.items.filter(v => v.name.toLowerCase().includes(s));
                                },
                                openList() { this.open = true; this.q = ''; },
                                pick(v) { this.confirmedLabel = v.name; this.q = v.name; this.open = false; $wire.pickVendor(v.id); },
                                cancel() { this.open = false; this.q = this.confirmedLabel; }
                            }"
                            x-on:click.outside="cancel()"
                        >
                            <input type="text" x-model="q"
                                   x-on:focus="openList()" x-on:click="openList()"
                                   x-on:input="open = true"
                                   x-on:keydown.enter.prevent.stop="if (filtered.length) { pick(filtered[0]); }"
                                   x-on:keydown.escape="cancel()"
                                   placeholder="Cari vendor..." autocomplete="off"
                                   class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <div x-show="open" x-cloak style="display:none;" class="absolute z-30 mt-1 w-full max-h-44 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                <template x-for="v in filtered" :key="v.id">
                                    <div x-on:mousedown.prevent="pick(v)" x-text="v.name" class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"></div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">Gak ketemu.</div>
                            </div>
                        </div>
                        @error('vendor_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Customer Terkait</label>
                        <select wire:model.live="customer_id" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <option value="">— gak dilink —</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Contact Person Vendor</label>
                    <select wire:model="contact_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                        <option value="">— gak ada PIC —</option>
                        @foreach ($contactOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                    @if (empty($contactOptions) && $vendor_id)
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Vendor ini belum ada data kontak — tambahin di menu Vendor kalau perlu.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Customer</label>
                        <div
                            class="relative"
                            x-data="{
                                open: false,
                                q: {{ \Illuminate\Support\Js::from($customer_id ? optional($customers->firstWhere('id', $customer_id))->name : '') }},
                                confirmedLabel: {{ \Illuminate\Support\Js::from($customer_id ? optional($customers->firstWhere('id', $customer_id))->name : '') }},
                                items: {{ \Illuminate\Support\Js::from($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values()) }},
                                get filtered() {
                                    if (! this.q) return this.items;
                                    const s = this.q.toLowerCase();
                                    return this.items.filter(c => c.name.toLowerCase().includes(s));
                                },
                                openList() { this.open = true; this.q = ''; },
                                pick(c) { this.confirmedLabel = c.name; this.q = c.name; this.open = false; $wire.pickCustomer(c.id); },
                                cancel() { this.open = false; this.q = this.confirmedLabel; }
                            }"
                            x-on:click.outside="cancel()"
                        >
                            <input type="text" x-model="q"
                                   x-on:focus="openList()" x-on:click="openList()"
                                   x-on:input="open = true"
                                   x-on:keydown.enter.prevent.stop="if (filtered.length) { pick(filtered[0]); }"
                                   x-on:keydown.escape="cancel()"
                                   placeholder="Cari customer..." autocomplete="off"
                                   class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <div x-show="open" x-cloak style="display:none;" class="absolute z-30 mt-1 w-full max-h-44 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                                <template x-for="c in filtered" :key="c.id">
                                    <div x-on:mousedown.prevent="pick(c)" x-text="c.name" class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"></div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400 dark:text-gray-500">Gak ketemu.</div>
                            </div>
                        </div>
                        @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Contact Person Customer</label>
                        <select wire:model="contact_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                            <option value="">— gak ada PIC —</option>
                            @foreach ($contactOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
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
                <button type="button" wire:click="addItem" class="text-xs font-semibold text-sky border border-sky/30 rounded-lg px-2.5 py-1.5">+ Tambah Baris</button>
            </div>

            <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-3">
                Isi Unit ATAU Credits sesuai kebutuhan tiap baris — kalau semua baris cuma pake Unit, kolom Credit gak bakal muncul di PDF. Begitu ada 1 baris aja yang isi Credits, kolom itu otomatis muncul di PDF.
            </p>

            <div class="space-y-3">
                @foreach ($items as $i => $item)
                    {{-- x-data lokal per baris — Qty x Unit Price dihitung LANGSUNG
                         di browser (Alpine), gak nunggu network round-trip ke
                         server dulu kayak sebelumnya (yang bikin Amount kelihatan
                         "gak update" sampe user ngelakuin aksi lain). Nilainya
                         tetep di-sync ke Livewire pake $wire.set(...,false)
                         (deferred, gak bikin request tiap ketik) buat disimpen
                         nanti pas Submit. --}}
                    <div
                        class="border border-gray-100 dark:border-gray-700 rounded-xl p-3 relative"
                        x-data="{
                            qty: {{ (float) ($item['qty'] ?: 0) }},
                            price: {{ (float) ($item['unit_price'] ?: 0) }},
                            get amount() { return (parseFloat(this.qty) || 0) * (parseFloat(this.price) || 0); }
                        }"
                    >
                        <button type="button" wire:click="removeItem({{ $i }})" class="absolute top-2 right-2 text-gray-300 dark:text-gray-600 hover:text-red-500 text-lg leading-none">&times;</button>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                            <input type="text" wire:model="items.{{ $i }}.group_label" placeholder="Label grup (opsional, cth. Option 1 - TrendAI Essentials)" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-xs">
                            @if ($type === 'po')
                                <input type="text" wire:model="items.{{ $i }}.product_type" placeholder="Product Type (cth. TrendAI Flex (credits))" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-xs">
                            @endif
                        </div>

                        <div class="mb-2">
                            <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Nama Item <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="items.{{ $i }}.item_name" placeholder="cth. TrendAI Flex (credits): VOMMMMMMXXLCZZZ" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm font-semibold">
                            @error("items.{$i}.item_name") <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Ditampilin BOLD di PDF sebagai nama item.</p>
                        </div>

                        <div class="mb-2">
                            <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Deskripsi Item <span class="font-normal">(opsional)</span></label>
                            <textarea wire:model="items.{{ $i }}.description" rows="2" placeholder="cth. Endpoint Security - Essential  65 credits per unit" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm"></textarea>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Kalau dikosongin, baris deskripsi ini gak bakal ditampilin di PDF.</p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Qty</label>
                                <input type="number" step="0.01" x-model="qty" x-on:input="$wire.set('items.{{ $i }}.qty', qty, false)" x-on:input.debounce.500ms="$wire.set('items.{{ $i }}.qty', qty, true)" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
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
                                <input type="number" step="1" x-model="price" x-on:input="$wire.set('items.{{ $i }}.unit_price', price, false)" x-on:input.debounce.500ms="$wire.set('items.{{ $i }}.unit_price', price, true)" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Amount</label>
                                <div class="text-sm font-mono font-semibold py-1.5" x-text="'Rp ' + Math.round(amount).toLocaleString('id-ID')"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <div class="text-right">
                    <div class="text-xs text-gray-400 dark:text-gray-500">Subtotal{{ $type === 'invoice' ? ' (sebelum pajak)' : '' }}</div>
                    <div class="font-mono font-bold text-lg">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- Pajak & Skema Pembayaran — khusus Invoice --}}
        @if ($type === 'invoice')
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-display font-bold text-sm">Pajak</div>
                    <button type="button" wire:click="addTax" class="text-xs font-semibold text-sky border border-sky/30 rounded-lg px-2.5 py-1.5">+ Tambah Pajak</button>
                </div>
                @if (empty($taxes))
                    <p class="text-xs text-gray-400 dark:text-gray-500">Belum ada pajak ditambahin (PPN, PPh, atau lain-lain).</p>
                @endif
                <div class="space-y-2">
                    @foreach ($taxes as $i => $tax)
                        <div class="flex items-end gap-2 flex-wrap">
                            <div class="flex-1 min-w-[110px]">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Nama Pajak</label>
                                <input type="text" wire:model="taxes.{{ $i }}.label" placeholder="cth. PPN 11% / PPh 23" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                            </div>
                            <div class="w-24">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Tipe</label>
                                <select wire:model.live="taxes.{{ $i }}.type" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                                    <option value="percentage">% Subtotal</option>
                                    <option value="fixed">Rp Tetap</option>
                                </select>
                            </div>
                            <div class="w-20">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Nilai</label>
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="taxes.{{ $i }}.value" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                            </div>
                            <div class="w-32">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Arah</label>
                                <select wire:model.live="taxes.{{ $i }}.direction" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                                    <option value="add">+ Tambah (PPN dst)</option>
                                    <option value="subtract">− Kurang (PPh dst)</option>
                                </select>
                            </div>
                            <div class="w-28 text-right shrink-0">
                                <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Hasil</label>
                                <div class="text-sm font-mono font-semibold py-1.5">{{ ($tax['direction'] ?? 'add') === 'subtract' ? '-' : '' }}Rp {{ number_format($taxAmounts[$i] ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <button type="button" wire:click="removeTax({{ $i }})" class="text-gray-300 dark:text-gray-600 hover:text-red-500 text-lg leading-none pb-1.5 shrink-0">&times;</button>
                        </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">
                    "Tambah" buat pajak yang nambah tagihan (PPN). "Kurang" buat pajak yang dipotong dari yang customer bayar (PPh 23, PPh Final, dst).
                </p>

                <div class="flex justify-end mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="text-right">
                        <div class="text-xs text-gray-400 dark:text-gray-500">Grand Total (Subtotal ± Pajak)</div>
                        <div class="font-mono font-bold text-lg">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                <div class="font-display font-bold text-sm mb-3">Skema Pembayaran</div>
                <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-sm mb-3">
                    <button type="button" wire:click="$set('payment_scheme', 'full')" class="px-3 py-1.5 rounded-md font-medium {{ $payment_scheme === 'full' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">Lunas</button>
                    <button type="button" wire:click="$set('payment_scheme', 'staged')" class="px-3 py-1.5 rounded-md font-medium {{ $payment_scheme === 'staged' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">DP / Termin</button>
                </div>

                @if ($payment_scheme === 'staged')
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-2">
                        Bebas dilabelin sendiri — misal "Down Payment 50%" buat DP, atau "Termin 1", "Termin 2" dst buat cicilan bertahap.
                    </p>
                    <div class="space-y-2">
                        @foreach ($paymentTerms as $i => $term)
                            <div class="flex items-end gap-2 flex-wrap">
                                <div class="flex-1 min-w-[120px]">
                                    <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Label</label>
                                    <input type="text" wire:model="paymentTerms.{{ $i }}.label" placeholder="cth. Down Payment / Termin 1" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                                </div>
                                <div class="w-24">
                                    <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">% Grand Total</label>
                                    <input type="number" step="0.01" wire:model.live.debounce.500ms="paymentTerms.{{ $i }}.percentage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2 py-1.5 text-sm">
                                </div>
                                <div class="w-28 text-right shrink-0">
                                    <label class="text-[10px] text-gray-400 dark:text-gray-500 block mb-0.5">Nominal</label>
                                    <div class="text-sm font-mono font-semibold py-1.5">Rp {{ number_format($paymentTermAmounts[$i] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <button type="button" wire:click="removePaymentTerm({{ $i }})" class="text-gray-300 dark:text-gray-600 hover:text-red-500 text-lg leading-none pb-1.5 shrink-0">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addPaymentTerm" class="text-xs font-semibold text-sky border border-sky/30 rounded-lg px-2.5 py-1.5 mt-2">+ Tambah Tahap</button>
                @endif
            </div>
        @endif

        {{-- Terms & signatory --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Terms and Condition</label>
                <textarea wire:model="terms" rows="6" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm font-mono"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Penandatangan</label>
                    <input type="text" wire:model="signatory_name" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Jabatan</label>
                    <input type="text" wire:model="signatory_title" placeholder="cth. Business Development" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between gap-3">
            @if ($editingId)
                <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus dokumen ini? Kalau ini nge-link ke opty, checklist Next Action terkait ikut ke-uncheck otomatis." class="text-sm font-semibold px-4 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 order-2 sm:order-1">Hapus Dokumen</button>
            @else
                <span class="hidden sm:inline"></span>
            @endif
            <div class="flex flex-col sm:flex-row gap-2 order-1 sm:order-2">
                <a href="{{ route('documents.index') }}" class="text-sm font-semibold px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-center">Batal</a>
                {{-- Simpan sebagai Draft ditaro TEPAT SETELAH Batal (bukan di
                     paling ujung) — dan ini yang jadi default kalau user
                     nekan Enter di form (form-nya wire:submit="saveDraft").
                     Draft gak ngecentang checklist Next Action di opty
                     terkait, baru Final yang ngecentang. --}}
                <button type="submit" class="text-sm font-semibold px-5 py-2 rounded-lg border border-sky text-sky hover:bg-sky/5">Simpan sebagai Draft</button>
                {{-- Simpan Dokumen (Final) SENGAJA type="button" + wire:click
                     manual, BUKAN submit form-nya — biar nekan Enter di
                     field manapun gak ke-anggep "Simpan Dokumen" (Final)
                     tapi ke-anggep Draft (lebih aman, gak "kecolongan" final). --}}
                <button type="button" wire:click="save" class="text-sm font-semibold px-5 py-2 rounded-lg bg-ink text-white">Simpan Dokumen</button>
            </div>
        </div>
    </form>
</div>
