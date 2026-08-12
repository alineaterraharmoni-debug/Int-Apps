<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category', 'stage', 'rating']);

        $opportunities = Opportunity::query()
            ->with(['sales', 'presales', 'engineers'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->get();

        $totalCount = $opportunities->count();
        $totalTcv = $opportunities->sum('tcv');
        $totalGpNominal = $opportunities->sum(fn ($o) => $o->gp_nominal);

        $byCategory = $opportunities->groupBy('category')->map(fn ($rows, $key) => [
            'label' => Opportunity::CATEGORIES[$key] ?? $key,
            'count' => $rows->count(),
            'tcv' => (float) $rows->sum('tcv'),
        ])->values();

        $byStage = $opportunities->groupBy('stage')->map(fn ($rows, $key) => [
            'label' => Opportunity::STAGES[$key] ?? $key,
            'count' => $rows->count(),
            'tcv' => (float) $rows->sum('tcv'),
        ])->values();

        $categoryChartUrl = $this->quickChartUrl([
            'type' => 'bar',
            'data' => [
                'labels' => $byCategory->pluck('label')->toArray(),
                'datasets' => [[
                    'label' => 'Jumlah Opty',
                    'data' => $byCategory->pluck('count')->toArray(),
                    'backgroundColor' => '#2AA9E0',
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
            ],
        ]);

        $stageChartUrl = $this->quickChartUrl([
            'type' => 'pie',
            'data' => [
                'labels' => $byStage->pluck('label')->toArray(),
                'datasets' => [[
                    'data' => $byStage->pluck('count')->toArray(),
                    'backgroundColor' => ['#94A3B8', '#2AA9E0', '#F2A93B', '#16A34A', '#DC2626'],
                ]],
            ],
        ]);

        $pdf = Pdf::loadView('pdf.report', [
            'filters' => $filters,
            'opportunities' => $opportunities,
            'totalCount' => $totalCount,
            'totalTcv' => $totalTcv,
            'totalGpNominal' => $totalGpNominal,
            'byCategory' => $byCategory,
            'byStage' => $byStage,
            'categoryChartUrl' => $categoryChartUrl,
            'stageChartUrl' => $stageChartUrl,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('opty-report-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * Build a static chart image URL via QuickChart.io.
     * DomPDF tidak bisa render <canvas>/JS, jadi grafik untuk PDF
     * digenerate sebagai gambar PNG lewat QuickChart.
     */
    private function quickChartUrl(array $chartConfig): string
    {
        $encoded = urlencode(json_encode($chartConfig));

        return "https://quickchart.io/chart?w=500&h=280&bkg=white&c={$encoded}";
    }
}
