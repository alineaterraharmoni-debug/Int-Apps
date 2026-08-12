<div>
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <div class="text-xs font-mono uppercase tracking-widest text-gray-400 mb-1">PT Alinea Terra Harmoni · Internal</div>
            <h1 class="font-display font-extrabold text-2xl">Report Opty</h1>
        </div>
        <a
            href="{{ route('report.export-pdf', array_filter([
                'date_from' => $date_from, 'date_to' => $date_to,
                'category' => $category, 'stage' => $stage, 'rating' => $rating,
            ])) }}"
            target="_blank"
            class="bg-ink text-white font-semibold text-sm px-4 py-2.5 rounded-lg hover:bg-gray-800"
        >
            Export PDF
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 mb-5 grid grid-cols-2 md:grid-cols-5 gap-3">
        <div>
            <label class="text-xs font-semibold text-gray-500 block mb-1">Dari Tanggal</label>
            <input type="date" wire:model.live="date_from" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-500 block mb-1">Sampai Tanggal</label>
            <input type="date" wire:model.live="date_to" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm">
        </div>
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
        <div class="col-span-2 md:col-span-5 flex justify-end">
            <button wire:click="resetFilters" class="text-xs font-semibold text-gray-500 hover:text-ink">Reset filter</button>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="text-xs font-semibold text-gray-500 mb-1">Total Number Opty</div>
            <div class="font-mono font-bold text-2xl">{{ $totalCount }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="text-xs font-semibold text-gray-500 mb-1">Total TCV</div>
            <div class="font-mono font-bold text-2xl">Rp {{ number_format($totalTcv, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="text-xs font-semibold text-gray-500 mb-1">Total GP Nominal</div>
            <div class="font-mono font-bold text-2xl">Rp {{ number_format($totalGpNominal, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">Jumlah Opty per Kategori</div>
            <canvas id="chartCategory" height="220"
                data-labels='@json($byCategory->pluck("label"))'
                data-values='@json($byCategory->pluck("count"))'></canvas>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl p-4">
            <div class="font-display font-bold text-sm mb-3">Distribusi per Stage</div>
            <canvas id="chartStage" height="220"
                data-labels='@json($byStage->pluck("label"))'
                data-values='@json($byStage->pluck("count"))'></canvas>
        </div>
    </div>

    {{-- Table by category --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 overflow-x-auto">
        <div class="font-display font-bold text-sm mb-3">Rincian per Kategori</div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2">Jumlah Opty</th>
                    <th class="pb-2">Total TCV</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($byCategory as $row)
                    <tr class="border-b border-gray-50">
                        <td class="py-2">{{ $row['label'] }}</td>
                        <td class="py-2 font-mono">{{ $row['count'] }}</td>
                        <td class="py-2 font-mono">Rp {{ number_format($row['tcv'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada data untuk filter ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('livewire:navigated', initCharts);
        document.addEventListener('livewire:initialized', initCharts);
        window.addEventListener('DOMContentLoaded', initCharts);
        document.addEventListener('livewire:updated', initCharts);

        let chartCategoryInstance, chartStageInstance;

        function initCharts() {
            const catEl = document.getElementById('chartCategory');
            const stgEl = document.getElementById('chartStage');
            if (!catEl || !stgEl) return;

            if (chartCategoryInstance) chartCategoryInstance.destroy();
            if (chartStageInstance) chartStageInstance.destroy();

            chartCategoryInstance = new Chart(catEl, {
                type: 'bar',
                data: {
                    labels: JSON.parse(catEl.dataset.labels),
                    datasets: [{ label: 'Jumlah Opty', data: JSON.parse(catEl.dataset.values), backgroundColor: '#2AA9E0' }],
                },
                options: { plugins: { legend: { display: false } }, responsive: true },
            });

            chartStageInstance = new Chart(stgEl, {
                type: 'pie',
                data: {
                    labels: JSON.parse(stgEl.dataset.labels),
                    datasets: [{ data: JSON.parse(stgEl.dataset.values), backgroundColor: ['#94A3B8', '#2AA9E0', '#F2A93B', '#16A34A', '#DC2626'] }],
                },
                options: { responsive: true },
            });
        }
    </script>
</div>
