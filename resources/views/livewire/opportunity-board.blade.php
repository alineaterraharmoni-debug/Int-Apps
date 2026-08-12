<div>
    <div class="flex items-center justify-between mb-5">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-2xl">Pipeline Opty</h1>
        </div>
        <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-gray-800">
            + Opty Baru
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-3" x-data="{}">
        @foreach ($stages as $key => $label)
            @php $items = $grouped[$key] ?? collect(); @endphp
            <div
                class="bg-gray-100 rounded-2xl p-3 min-h-[140px]"
                x-on:dragover.prevent="$el.classList.add('ring-2','ring-sky-400')"
                x-on:dragleave="$el.classList.remove('ring-2','ring-sky-400')"
                x-on:drop.prevent="
                    $el.classList.remove('ring-2','ring-sky-400');
                    $wire.moveStage(parseInt($event.dataTransfer.getData('text/plain')), '{{ $key }}')
                "
            >
                <div class="flex items-center justify-between mb-3 px-1">
                    <div>
                        <div class="font-display font-bold text-sm">{{ $label }}</div>
                        <div class="text-xs font-mono text-gray-400">
                            Rp {{ number_format($items->sum('tcv'), 0, ',', '.') }}
                        </div>
                    </div>
                    <span class="text-xs font-mono bg-white border border-gray-200 rounded-full px-2 py-0.5">
                        {{ $items->count() }}
                    </span>
                </div>

                @forelse ($items as $opty)
                    <div
                        draggable="true"
                        x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $opty->id }}')"
                        wire:click="openEdit({{ $opty->id }})"
                        wire:key="opty-{{ $opty->id }}"
                        class="bg-white border border-gray-200 rounded-xl p-3 mb-2 cursor-pointer hover:shadow-md transition"
                    >
                        <div class="font-display font-bold text-sm leading-snug">{{ $opty->title }}</div>
                        <div class="text-xs text-gray-500 mb-2">{{ $opty->customer_name }}</div>

                        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 text-sky-600">
                                {{ $opty->category_label }}
                            </span>
                            <span @class([
                                'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                'bg-red-50 text-red-600' => $opty->rating === 'high',
                                'bg-amber-50 text-amber-600' => $opty->rating === 'med',
                                'bg-gray-100 text-gray-500' => $opty->rating === 'low',
                            ])>
                                {{ $opty->rating_label }}
                            </span>
                        </div>

                        <div class="font-mono font-semibold text-sm mb-1">
                            Rp {{ number_format($opty->tcv, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 font-mono mb-1">
                            GP {{ rtrim(rtrim(number_format($opty->gp_percentage, 1), '0'), '.') }}% ·
                            Rp {{ number_format($opty->gp_nominal, 0, ',', '.') }}
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-400 mt-2">
                            <span>{{ $opty->sales?->name ?? '—' }}</span>
                            <span>{{ $opty->expected_closing_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-xs text-gray-400 py-6">Belum ada opty</div>
                @endforelse
            </div>
        @endforeach
    </div>

    {{-- ===== Modal Form ===== --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white rounded-2xl w-full max-w-xl max-h-[88vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Opty' : 'Opty Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Judul Opty</label>
                        <input type="text" wire:model="title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="cth. Firewall Renewal - Bank XYZ">
                        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Nama Customer</label>
                            <input type="text" wire:model="customer_name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('customer_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Lini Produk</label>
                            <select wire:model="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                @foreach ($categories as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Estimasi TCV (Rp)</label>
                            <input type="number" step="1000000" min="0" wire:model="tcv" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('tcv') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">GP (% dari TCV)</label>
                            <input type="number" step="0.1" min="0" max="100" wire:model="gp_percentage" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @error('gp_percentage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Rating</label>
                            <select wire:model="rating" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                @foreach ($ratings as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Stage</label>
                            <select wire:model="stage" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                @foreach ($stages as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Ekspektasi Closing</label>
                            <input type="date" wire:model="expected_closing_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Sales (assigned)</label>
                            <select wire:model="sales_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                <option value="">— pilih sales —</option>
                                @foreach ($salesOptions as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 block mb-1">Presales / Tim Produk</label>
                            <select wire:model="presales_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                <option value="">— pilih presales —</option>
                                @foreach ($presalesOptions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">
                            Estimasi Tim Engineer (isi kalau sudah Close WIN)
                        </label>
                        <select wire:model="engineer_ids" multiple class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm h-24">
                            @foreach ($engineerOptions as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Ctrl/Cmd + klik untuk pilih lebih dari satu.</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Next Action</label>
                        <input type="text" wire:model="next_action" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 block mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus opty ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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
            </div>
        </div>
    @endif
</div>
