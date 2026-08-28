<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">CRM · Business Review</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Report {{ $range['label'] }}</h1>
        </div>
        <a
            href="{{ route('crm.report.export-pdf', array_filter([
                'period' => $period, 'year' => $year, 'month' => $month, 'quarter' => $quarter,
                'custom_from' => $custom_from, 'custom_to' => $custom_to,
                'category' => $category, 'stage' => $stage, 'rating' => $rating, 'customer_id' => $customer_id,
            ])) }}"
            class="inline-flex items-center gap-1.5 bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap"
        >
            <x-icon name="download" class="w-4 h-4" />
            Export PDF
        </a>
    </div>

    {{-- Period selector — selalu keliatan (ini yang paling sering diganti-ganti) --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-3">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 text-sm flex-wrap">
                @foreach (['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan', 'custom' => 'Custom Range'] as $val => $label)
                    <button
                        wire:click="setPeriod('{{ $val }}')"
                        class="px-3 py-1.5 rounded-md font-medium whitespace-nowrap {{ $period === $val ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            @if ($period === 'monthly')
                <select wire:model.live="month" class="border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                    @foreach (range(1,12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            @elseif ($period === 'quarterly')
                <select wire:model.live="quarter" class="border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                    @foreach ([1,2,3,4] as $q)
                        <option value="{{ $q }}">Q{{ $q }}</option>
                    @endforeach
                </select>
            @endif
            @if (in_array($period, ['monthly', 'quarterly', 'yearly']))
                <select wire:model.live="year" class="border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                    @foreach (range(now()->year, now()->year - 4) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            @endif
            @if ($period === 'custom')
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="custom_from" class="border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                    <span class="text-gray-400 text-sm">s/d</span>
                    <input type="date" wire:model.live="custom_to" class="border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-1.5 text-sm">
                </div>
            @endif
        </div>
    </div>

    {{-- Filter tambahan — dipisah jadi panel collapsible (konsisten sama pola
         di menu Board), biar gak numpuk 5 dropdown selalu keliatan di HP. --}}
    @php
        $activeFilterCount = collect([$category, $stage, $rating, $customer_id])->filter(fn ($v) => ! is_null($v) && $v !== '')->count();
    @endphp
    <div class="mb-5">
        <button wire:click="$toggle('showFilters')" class="relative inline-flex items-center gap-1.5 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 {{ $showFilters ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
            <x-icon name="sliders" class="w-4 h-4" />
            Filter Tambahan
            @if ($activeFilterCount)
                <span class="absolute -top-1.5 -right-1.5 bg-sky text-navy text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $activeFilterCount }}</span>
            @endif
        </button>

        @if ($showFilters)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mt-2 grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Lini Produk</label>
                    <select wire:model.live="category" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($categories as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Stage</label>
                    <select wire:model.live="stage" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($stages as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Rating</label>
                    <select wire:model.live="rating" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($ratings as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1">Customer</label>
                    <select wire:model.live="customer_id" class="w-full border border-gray-200 dark:border-gray-700 dark:bg-gray-900 rounded-lg px-2.5 py-2 text-sm">
                        <option value="">Semua</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($activeFilterCount)
                    <div class="col-span-2 md:col-span-4 pt-1">
                        <button wire:click="resetFilters" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                            <x-icon name="x" class="w-3 h-3" /> Reset semua filter
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Summary cards w/ growth vs periode sebelumnya --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        @php
            $cards = [
                ['label' => 'Total Number Opty', 'value' => $current['totalCount'], 'growth' => $growth['count'], 'format' => 'int'],
                ['label' => 'Total TCV', 'value' => $current['totalTcv'], 'growth' => $growth['tcv'], 'format' => 'rp'],
                ['label' => 'Total GP Nominal', 'value' => $current['totalGpNominal'], 'growth' => $growth['gp'], 'format' => 'rp'],
                ['label' => 'Total Closing WON', 'value' => $current['wonTcv'], 'growth' => $growth['won_tcv'], 'format' => 'rp'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ $card['label'] }}</div>
                <div class="font-mono font-bold text-xl">
                    {{ $card['format'] === 'rp' ? 'Rp '.number_format($card['value'], 0, ',', '.') : $card['value'] }}
                </div>
                @if (! is_null($card['growth']))
                    <div class="text-xs font-mono mt-1 {{ $card['growth'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $card['growth'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($card['growth']), 1) }}% vs periode lalu
                    </div>
                @else
                    <div class="text-xs font-mono mt-1 text-gray-400 dark:text-gray-500">— vs periode lalu</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Detail opty hasil filter --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 mb-5">
        <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
            <div class="font-display font-bold text-sm">
                Detail Opty ({{ $current['rows']->count() }}) — {{ $range['label'] }}
            </div>
            <a
                href="{{ route('crm.report.export-csv', array_filter([
                    'period' => $period, 'year' => $year, 'month' => $month, 'quarter' => $quarter,
                    'custom_from' => $custom_from, 'custom_to' => $custom_to,
                    'category' => $category, 'stage' => $stage, 'rating' => $rating, 'customer_id' => $customer_id,
                ])) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                title="Export data mentah ke Excel (CSV)"
            >
                <x-icon name="download" class="w-3.5 h-3.5" />
                Export Excel
            </a>
        </div>

        {{-- Tabel di tablet/desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-2">Nama Opty</th>
                        <th class="pb-2">Customer</th>
                        <th class="pb-2">Stage</th>
                        <th class="pb-2">Rating</th>
                        <th class="pb-2">Nilai TCV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detailRows as $opty)
                        <tr class="border-b border-gray-50 dark:border-gray-700/60">
                            <td class="py-2 font-medium">{{ $opty->title }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400">{{ $opty->customer?->name ?? $opty->customer_name }}</td>
                            <td class="py-2">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky">{{ $opty->stage_label }}</span>
                            </td>
                            <td class="py-2">
                                <span @class([
                                    'text-[10px] font-semibold px-2 py-0.5 rounded-full',
                                    'bg-red-50 dark:bg-red-500/10 text-red-600' => $opty->rating === 'high',
                                    'bg-amber-50 dark:bg-amber/10 text-amber-600' => $opty->rating === 'med',
                                    'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                                ])>{{ $opty->rating_label }}</span>
                            </td>
                            <td class="py-2 font-mono">Rp {{ number_format($opty->tcv, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400 dark:text-gray-500">Belum ada opty untuk periode/filter ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card list di mobile — 5 kolom gak bakal muat rapi di HP tanpa
             scroll horizontal yang bikin nama opty/customer kepotong. --}}
        <div class="md:hidden -mx-4 divide-y-2 divide-gray-100 dark:divide-gray-700">
            @forelse ($detailRows as $opty)
                <div class="px-4 py-3">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="font-medium text-sm leading-snug truncate">{{ $opty->title }}</div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky/10 text-sky shrink-0">{{ $opty->stage_label }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $opty->customer?->name ?? $opty->customer_name }}</span>
                        <span @class([
                            'text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0',
                            'bg-red-50 dark:bg-red-500/10 text-red-600' => $opty->rating === 'high',
                            'bg-amber-50 dark:bg-amber/10 text-amber-600' => $opty->rating === 'med',
                            'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' => $opty->rating === 'low',
                        ])>{{ $opty->rating_label }}</span>
                    </div>
                    <div class="font-mono font-semibold text-sm">Rp {{ number_format($opty->tcv, 0, ',', '.') }}</div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada opty untuk periode/filter ini</div>
            @endforelse
        </div>

        {{-- Pagination manual — Detail Opty di-slice per 25 baris biar gak berat
             kalau datanya banyak dalam satu periode. --}}
        @if ($detailTotalPages > 1)
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <span class="text-xs text-gray-400 dark:text-gray-500">Halaman {{ $detailPage }} dari {{ $detailTotalPages }}</span>
                <div class="flex items-center gap-2">
                    <button
                        wire:click="goToDetailPage({{ $detailPage - 1 }})"
                        @disabled($detailPage <= 1)
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed"
                    >← Sebelumnya</button>
                    <button
                        wire:click="goToDetailPage({{ $detailPage + 1 }})"
                        @disabled($detailPage >= $detailTotalPages)
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed"
                    >Selanjutnya →</button>
                </div>
            </div>
        @endif
    </div>

    {{-- Charts --}}
    <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
        <div class="font-display font-bold text-sm">Grafik</div>
        <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-0.5 text-xs">
            <button wire:click="setChartMetric('count')" class="px-2.5 py-1.5 rounded-md font-medium {{ $chartMetric === 'count' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">Jumlah Opty</button>
            <button wire:click="setChartMetric('tcv')" class="px-2.5 py-1.5 rounded-md font-medium {{ $chartMetric === 'tcv' ? 'bg-white dark:bg-gray-800 shadow text-ink dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">Nilai TCV</button>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">{{ $chartMetric === 'tcv' ? 'Nilai TCV per Kategori' : 'Jumlah Opty per Kategori' }}</div>
            <canvas id="chartCategory" height="220"
                data-labels='@json($current["byCategory"]->pluck("label"))'
                data-values='@json($current["byCategory"]->pluck($chartMetric === "tcv" ? "tcv" : "count"))'
                data-metric="{{ $chartMetric }}"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">{{ $chartMetric === 'tcv' ? 'Distribusi Nilai per Stage' : 'Distribusi Jumlah per Stage' }}</div>
            <canvas id="chartStage" height="220"
                data-labels='@json($current["byStage"]->pluck("label"))'
                data-values='@json($current["byStage"]->pluck($chartMetric === "tcv" ? "tcv" : "count"))'
                data-metric="{{ $chartMetric }}"></canvas>
        </div>
    </div>

    {{-- Table by category --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
        <div class="font-display font-bold text-sm mb-3">Rincian per Kategori — {{ $range['label'] }}</div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-700">
                        <th class="pb-2">Kategori</th>
                        <th class="pb-2">Jumlah Opty</th>
                        <th class="pb-2">Total TCV</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($current['byCategory'] as $row)
                        <tr class="border-b border-gray-50 dark:border-gray-700/60">
                            <td class="py-2">{{ $row['label'] }}</td>
                            <td class="py-2 font-mono">{{ $row['count'] }}</td>
                            <td class="py-2 font-mono">Rp {{ number_format($row['tcv'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400 dark:text-gray-500">Belum ada data untuk periode/filter ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden -mx-4 divide-y-2 divide-gray-100 dark:divide-gray-700">
            @forelse ($current['byCategory'] as $row)
                <div class="px-4 py-2.5 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-sm font-medium truncate">{{ $row['label'] }}</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $row['count'] }} opty</div>
                    </div>
                    <div class="font-mono font-semibold text-sm shrink-0">Rp {{ number_format($row['tcv'], 0, ',', '.') }}</div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Belum ada data untuk periode/filter ini</div>
            @endforelse
        </div>
    </div>

    <script>
        function initReportCharts() {
            const catEl = document.getElementById('chartCategory');
            const stgEl = document.getElementById('chartStage');
            if (!catEl || !stgEl || typeof Chart === 'undefined') return;

            if (window.__chartCategory) window.__chartCategory.destroy();
            if (window.__chartStage) window.__chartStage.destroy();

            const fmtValue = (metric, raw) => metric === 'tcv' ? ('Rp ' + Number(raw).toLocaleString('id-ID')) : (raw + ' opty');

            const catMetric = catEl.dataset.metric;
            window.__chartCategory = new Chart(catEl, {
                type: 'bar',
                data: {
                    labels: JSON.parse(catEl.dataset.labels),
                    datasets: [{
                        label: catMetric === 'tcv' ? 'Total TCV (Rp)' : 'Jumlah Opty',
                        data: JSON.parse(catEl.dataset.values),
                        backgroundColor: '#2AA9E0',
                    }],
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => fmtValue(catMetric, ctx.raw) } },
                    },
                    responsive: true,
                },
            });

            const stgMetric = stgEl.dataset.metric;
            window.__chartStage = new Chart(stgEl, {
                type: 'pie',
                data: {
                    labels: JSON.parse(stgEl.dataset.labels),
                    datasets: [{ data: JSON.parse(stgEl.dataset.values), backgroundColor: ['#19A9DB', '#F6B01A', '#16A34A', '#DC2626'] }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + fmtValue(stgMetric, ctx.raw) } },
                    },
                },
            });
        }

        // Livewire 3 gak nge-dispatch event DOM 'livewire:updated' kayak v2 dulu —
        // itu sebabnya chart lama gak pernah re-render pas filter ganti.
        // Fix-nya: dengerin custom event 'report-updated' yang di-dispatch dari PHP.
        document.addEventListener('livewire:init', () => {
            Livewire.on('report-updated', () => initReportCharts());
        });
        document.addEventListener('livewire:navigated', initReportCharts);
        window.addEventListener('DOMContentLoaded', initReportCharts);
    </script>
</div>
