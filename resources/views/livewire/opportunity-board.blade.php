<div>
    <div class="flex items-center justify-between mb-4 md:mb-5">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Pipeline Opty</h1>
        </div>
        <div class="flex items-center gap-2">
            <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-xs">
                <button wire:click="setViewMode('board')" class="px-2.5 py-1.5 rounded-md font-medium flex items-center gap-1 {{ $viewMode === 'board' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                    <x-icon name="layout-kanban" class="w-3.5 h-3.5" /> <span class="hidden sm:inline">Board</span>
                </button>
                <button wire:click="setViewMode('list')" class="px-2.5 py-1.5 rounded-md font-medium flex items-center gap-1 {{ $viewMode === 'list' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                    <x-icon name="file-text" class="w-3.5 h-3.5" /> <span class="hidden sm:inline">List</span>
                </button>
            </div>
            @if ($canCreateOrEdit)
                <button wire:click="openCreate" class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap">
                    + Opty Baru
                </button>
            @else
                <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">Mode lihat aja</span>
            @endif
        </div>
    </div>

    @if ($viewMode === 'board')
    <div class="text-[11px] text-gray-400 dark:text-gray-500 mb-2 md:hidden">
        Geser ke samping buat lihat stage lainnya → · Tap kartu buat ubah stage/detail
    </div>

    <div
        class="flex md:grid md:grid-cols-4 gap-3 overflow-x-auto md:overflow-visible snap-x snap-mandatory -mx-3 px-3 md:mx-0 md:px-0 pb-2"
        x-data="{}"
    >
        @php
            $stageColors = [
                'leads' => ['bar' => 'bg-slate-400', 'text' => 'text-slate-500 dark:text-slate-300', 'bg' => 'bg-slate-50 dark:bg-slate-500/10'],
                'develop' => ['bar' => 'bg-amber-400', 'text' => 'text-amber-600 dark:text-amber-300', 'bg' => 'bg-amber-50 dark:bg-amber-500/10'],
                'won' => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-300', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10'],
                'lost' => ['bar' => 'bg-rose-500', 'text' => 'text-rose-600 dark:text-rose-300', 'bg' => 'bg-rose-50 dark:bg-rose-500/10'],
            ];
        @endphp
        @foreach ($stages as $key => $label)
            @php
                $items = $grouped[$key] ?? collect();
                $sc = $stageColors[$key] ?? $stageColors['leads'];
            @endphp
            <div
                class="{{ $sc['bg'] }} border-2 border-transparent rounded-2xl p-3 min-h-[140px] shrink-0 w-[82vw] sm:w-[60vw] md:w-auto snap-start relative overflow-hidden"
                x-on:dragover.prevent="$el.classList.add('ring-2','ring-sky-400')"
                x-on:dragleave="$el.classList.remove('ring-2','ring-sky-400')"
                x-on:drop.prevent="
                    $el.classList.remove('ring-2','ring-sky-400');
                    $wire.moveStage(parseInt($event.dataTransfer.getData('text/plain')), '{{ $key }}')
                "
            >
                <span class="absolute top-0 left-0 right-0 h-1 {{ $sc['bar'] }}"></span>
                <div class="flex items-center justify-between mb-3 px-1 pt-1">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $sc['bar'] }}"></span>
                            <div class="font-display font-bold text-sm {{ $sc['text'] }}">{{ $label }}</div>
                        </div>
                        <div class="text-xs font-mono text-gray-400 dark:text-gray-500 ml-3.5">
                            Rp {{ number_format($items->sum('tcv'), 0, ',', '.') }}
                        </div>
                    </div>
                    <span class="text-xs font-mono bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full px-2 py-0.5">
                        {{ $items->count() }}
                    </span>
                </div>

                @forelse ($items as $opty)
                    @php
                        $cardEditable = $canManageFull || ($canManageMqlOnly && $opty->stage === 'leads');
                    @endphp
                    <div
                        class="opty-card"
                        style="touch-action: manipulation;"
                        x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $opty->id }}')"
                        @if ($cardEditable) wire:click="openEdit({{ $opty->id }})" @endif
                        wire:key="opty-{{ $opty->id }}"
                        @class([
                            'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 mb-2 transition',
                            'cursor-pointer hover:shadow-md' => $cardEditable,
                            'opacity-80' => ! $cardEditable,
                        ])
                    >
                        <div class="font-display font-bold text-sm leading-snug">{{ $opty->title }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $opty->customer?->name ?? $opty->customer_name }}</div>

                        <div class="flex items-center gap-1.5 mb-2 flex-wrap">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 text-sky-600">
                                {{ $opty->category_label }}
                            </span>
                            <span @class([
                                'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                'bg-red-50 text-red-600' => $opty->rating === 'high',
                                'bg-amber-50 text-amber-600' => $opty->rating === 'med',
                                'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                            ])>
                                {{ $opty->rating_label }}
                            </span>
                        </div>

                        <div class="font-mono font-semibold text-sm mb-1">
                            Rp {{ number_format($opty->tcv, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-gray-400 dark:text-gray-500 font-mono mb-1">
                            GP {{ rtrim(rtrim(number_format($opty->gp_percentage, 1), '0'), '.') }}% ·
                            Rp {{ number_format($opty->gp_nominal, 0, ',', '.') }}
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500 mt-2">
                            <span>{{ $opty->sales?->name ?? '—' }}</span>
                            <span>{{ $opty->expected_closing_date?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-xs text-gray-400 dark:text-gray-500 py-6">Belum ada opty</div>
                @endforelse
            </div>
        @endforeach
    </div>
    @else
        {{-- ===== List View ===== --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-4 grid grid-cols-3 gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Filter Stage</label>
                <select wire:model.live="listFilterStage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($stages as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Filter Rating</label>
                <select wire:model.live="listFilterRating" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($ratings as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Per Halaman</label>
                <select wire:model.live="listPerPage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-x-auto">
            <table class="w-full text-sm min-w-[480px]">
                <thead>
                    <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                        <th class="p-3">Nama Opty</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Stage</th>
                        <th class="p-3">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listItems as $opty)
                        @php $rowEditable = $canManageFull || ($canManageMqlOnly && $opty->stage === 'leads'); @endphp
                        <tr
                            wire:key="list-opty-{{ $opty->id }}"
                            @if ($rowEditable) wire:click="openEdit({{ $opty->id }})" @endif
                            @class([
                                'border-b border-gray-50 dark:border-gray-700/60 transition',
                                'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/40' => $rowEditable,
                            ])
                        >
                            <td class="p-3 font-medium">{{ $opty->title }}</td>
                            <td class="p-3 text-gray-500 dark:text-gray-400">{{ $opty->customer?->name ?? $opty->customer_name }}</td>
                            <td class="p-3">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $opty->stage_label }}</span>
                            </td>
                            <td class="p-3">
                                <span @class([
                                    'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                    'bg-red-50 dark:bg-red-500/10 text-red-600' => $opty->rating === 'high',
                                    'bg-amber-50 dark:bg-amber/10 text-amber-600' => $opty->rating === 'med',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                                ])>{{ $opty->rating_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-gray-400 dark:text-gray-500">Belum ada opty untuk filter ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
            <div class="text-xs text-gray-400 dark:text-gray-500">
                Nampilin {{ $listItems->firstItem() ?? 0 }}–{{ $listItems->lastItem() ?? 0 }} dari {{ $listItems->total() }} opty
            </div>
            <div>
                {{ $listItems->links() }}
            </div>
        </div>
    @endif
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center sm:p-4 z-50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-2xl w-full max-w-xl max-h-[92vh] sm:max-h-[88vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-extrabold text-lg">{{ $editingId ? 'Edit Opty' : 'Opty Baru' }}</h2>
                    <button wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 text-xl leading-none">&times;</button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Judul Opty</label>
                        <input type="text" wire:model="title" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm" placeholder="cth. Firewall Renewal - Bank XYZ">
                        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Nama Customer</label>
                            @if (! $showQuickAddCustomer)
                                <select wire:model="customer_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    <option value="">— pilih customer —</option>
                                    @foreach ($customerOptions as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="$set('showQuickAddCustomer', true)" class="text-[11px] text-sky font-semibold mt-1">
                                    + Customer baru
                                </button>
                            @else
                                <div class="flex gap-1.5">
                                    <input type="text" wire:model="new_customer_name" placeholder="Nama customer baru" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    <button type="button" wire:click="quickAddCustomer" class="text-xs font-semibold px-3 rounded-lg bg-ink text-white whitespace-nowrap">Simpan</button>
                                </div>
                                <button type="button" wire:click="$set('showQuickAddCustomer', false)" class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Batal</button>
                            @endif
                            @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @error('new_customer_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Lini Produk</label>
                            <select wire:model="category" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                @foreach ($categories as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Estimasi TCV (Rp)</label>
                            <input type="number" step="1000000" min="0" wire:model="tcv" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                            @error('tcv') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">GP (% dari TCV)</label>
                            <input type="number" step="0.1" min="0" max="100" wire:model="gp_percentage" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                            @error('gp_percentage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Rating</label>
                            <select wire:model="rating" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                @foreach ($ratings as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Stage</label>
                            @if ($canManageFull)
                                <select wire:model.live="stage" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                    @foreach ($stages as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" value="Leads" disabled class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900/40 text-gray-400 dark:text-gray-500">
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Role lo dibatasin cuma sampai stage Leads.</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Ekspektasi Closing</label>
                            <input type="date" wire:model="expected_closing_date" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    @if ($stage === 'lost')
                        <div class="bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 rounded-xl p-3">
                            @if ($promptingLostReason)
                                <p class="text-xs font-semibold text-rose-700 dark:text-rose-300 mb-2">
                                    Opty ini baru dipindah ke Lost — isi alasannya biar kepake buat evaluasi nanti.
                                </p>
                            @endif
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Kategori Alasan Drop</label>
                            <select wire:model="lost_category" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm mb-2">
                                <option value="">— pilih alasan —</option>
                                @foreach ($lostCategories as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('lost_category') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 block mb-1">Detail Tambahan (opsional)</label>
                            <textarea wire:model="lost_reason" rows="2" placeholder="cth. Customer pilih kompetitor karena harga 15% lebih murah" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Sales (assigned)</label>
                            <select wire:model="sales_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                <option value="">— pilih sales —</option>
                                @foreach ($salesOptions as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Presales / Tim Produk</label>
                            <select wire:model="presales_id" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                                <option value="">— pilih presales —</option>
                                @foreach ($presalesOptions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">
                            Estimasi Tim Engineer (isi kalau sudah Close WIN)
                        </label>
                        <select wire:model="engineer_ids" multiple class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm h-24">
                            @foreach ($engineerOptions as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Ctrl/Cmd + klik untuk pilih lebih dari satu.</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Next Action</label>
                        <input type="text" wire:model="next_action" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="3" class="w-full border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        @if ($editingId && $canManageFull)
                            <button type="button" wire:click="delete" wire:confirm="Yakin mau hapus opty ini?" class="text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold px-4 py-2 rounded-lg">
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

<script>
    // Fix bug edit/drag di PWA & HP: draggable="true" yang statis dari server
    // bikin sebagian browser (terutama mode PWA standalone) bingung bedain
    // tap vs drag, jadi wire:click gak pernah kepencet. Solusinya: draggable
    // cuma diaktifin buat device yang beneran punya mouse (pointer: fine).
    function applyDraggable() {
        const isMouse = window.matchMedia('(pointer: fine)').matches;
        document.querySelectorAll('.opty-card').forEach((el) => {
            if (isMouse) {
                el.setAttribute('draggable', 'true');
            } else {
                el.removeAttribute('draggable');
            }
        });
    }
    applyDraggable();
    document.addEventListener('livewire:navigated', applyDraggable);
    document.addEventListener('livewire:init', () => {
        Livewire.on('board-updated', () => applyDraggable());
    });
</script>
