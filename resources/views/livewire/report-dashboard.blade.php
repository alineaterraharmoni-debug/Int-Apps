<div>
    <div class="flex items-center justify-between mb-4 md:mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">CRM · Business Review</div>
            <h1 class="font-display font-extrabold text-xl md:text-2xl">Report {{ $range['label'] }}</h1>
        </div>
        <a
            href="{{ route('crm.report.export-pdf', array_filter([
                'period' => $period, 'year' => $year, 'month' => $month, 'quarter' => $quarter,
                'category' => $category, 'stage' => $stage, 'rating' => $rating,
            ])) }}"
            target="_blank"
            class="bg-ink text-white font-semibold text-sm px-3.5 md:px-4 py-2 md:py-2.5 rounded-lg hover:bg-gray-800 whitespace-nowrap"
        >
            Export PDF
        </a>
    </div>

    {{-- Period selector --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-5">
        <div class="flex flex-wrap items-center gap-3 mb-3">
            <div class="inline-flex bg-gray-100 rounded-lg p-1 text-sm">
                @foreach (['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', 'yearly' => 'Tahunan'] as $val => $label)
                    <button
                        wire:click="setPeriod('{{ $val }}')"
                        class="px-3 py-1.5 rounded-md font-medium {{ $period === $val ? 'bg-white shadow text-ink' : 'text-gray-500' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            @if ($period === 'monthly')
                <select wire:model.live="month" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm">
                    @foreach (range(1,12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            @elseif ($period === 'quarterly')
                <select wire:model.live="quarter" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm">
                    @foreach ([1,2,3,4] as $q)
                        <option value="{{ $q }}">Q{{ $q }}</option>
                    @endforeach
                </select>
            @endif
            <select wire:model.live="year" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm">
                @foreach (range(now()->year, now()->year - 4) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-gray-100">
            <div>
                <label class="text-xs font-semibold text-gray-500 block mb-1">Lini Produk</label>
                <select wire:model.live="category" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($categories as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 block mb-1">Stage</label>
                <select wire:model.live="stage" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($stages as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 block mb-1">Rating</label>
                <select wire:model.live="rating" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
                    <option value="">Semua</option>
                    @foreach ($ratings as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="resetFilters" class="text-xs font-semibold text-gray-500 hover:text-ink">Reset filter</button>
            </div>
        </div>
    </div>

    {{-- Summary cards w/ growth vs periode sebelumnya --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
        @php
            $cards = [
                ['label' => 'Total Number Opty', 'value' => $current['totalCount'], 'growth' => $growth['count'], 'format' => 'int'],
                ['label' => 'Total TCV', 'value' => $current['totalTcv'], 'growth' => $growth['tcv'], 'format' => 'rp'],
                ['label' => 'Total GP Nominal', 'value' => $current['totalGpNominal'], 'growth' => $growth['gp'], 'format' => 'rp'],
                ['label' => 'Total Closing WON', 'value' => $current['wonTcv'], 'growth' => $growth['won_tcv'], 'format' => 'rp'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="bg-white border border-gray-200 rounded-2xl p-4">
                <div class="text-xs font-semibold text-gray-500 mb-1">{{ $card['label'] }}</div>
                <div class="font-mono font-bold text-xl">
                    {{ $card['format'] === 'rp' ? 'Rp '.number_format($card['value'], 0, ',', '.') : $card['value'] }}
                </div>
                @if (! is_null($card['growth']))
                    <div class="text-xs font-mono mt-1 {{ $card['growth'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $card['growth'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($card['growth']), 1) }}% vs periode lalu
                    </div>
                @else
                    <div class="text-xs font-mono mt-1 text-gray-400">— vs periode lalu</div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">Jumlah Opty per Kategori</div>
            <canvas id="chartCategory" height="220"
                data-labels='@json($current["byCategory"]->pluck("label"))'
                data-values='@json($current["byCategory"]->pluck("count"))'></canvas>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">Distribusi per Stage</div>
            <canvas id="chartStage" height="220"
                data-labels='@json($current["byStage"]->pluck("label"))'
                data-values='@json($current["byStage"]->pluck("count"))'></canvas>
        </div>
    </div>

    {{-- Table by category --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 overflow-x-auto">
        <div class="font-display font-bold text-sm mb-3">Rincian per Kategori — {{ $range['label'] }}</div>
        <table class="w-full text-sm min-w-[420px]">
            <thead>
                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2">Jumlah Opty</th>
                    <th class="pb-2">Total TCV</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($current['byCategory'] as $row)
                    <tr class="border-b border-gray-50">
                        <td class="py-2">{{ $row['label'] }}</td>
                        <td class="py-2 font-mono">{{ $row['count'] }}</td>
                        <td class="py-2 font-mono">Rp {{ number_format($row['tcv'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada data untuk periode/filter ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function initReportCharts() {
            const catEl = document.getElementById('chartCategory');
            const stgEl = document.getElementById('chartStage');
            if (!catEl || !stgEl || typeof Chart === 'undefined') return;

            if (window.__chartCategory) window.__chartCategory.destroy();
            if (window.__chartStage) window.__chartStage.destroy();

            window.__chartCategory = new Chart(catEl, {
                type: 'bar',
                data: {
                    labels: JSON.parse(catEl.dataset.labels),
                    datasets: [{ label: 'Jumlah Opty', data: JSON.parse(catEl.dataset.values), backgroundColor: '#2AA9E0' }],
                },
                options: { plugins: { legend: { display: false } }, responsive: true },
            });

            window.__chartStage = new Chart(stgEl, {
                type: 'pie',
                data: {
                    labels: JSON.parse(stgEl.dataset.labels),
                    datasets: [{ data: JSON.parse(stgEl.dataset.values), backgroundColor: ['#94A3B8', '#2AA9E0', '#F2A93B', '#16A34A', '#DC2626'] }],
                },
                options: { responsive: true },
            });
        }
        document.addEventListener('livewire:navigated', initReportCharts);
        document.addEventListener('livewire:initialized', initReportCharts);
        window.addEventListener('DOMContentLoaded', initReportCharts);
        document.addEventListener('livewire:updated', initReportCharts);
    </script>
</div>
