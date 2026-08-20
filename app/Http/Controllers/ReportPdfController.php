<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Services\OpportunityReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    public function export(Request $request, OpportunityReportService $service)
    {
        $period = $request->get('period', 'monthly');
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $quarter = (int) $request->get('quarter', ceil(now()->month / 3));
        $customFrom = $request->get('custom_from');
        $customTo = $request->get('custom_to');

        $range = $service->resolvePeriod($period, $year, $month, $quarter, $customFrom, $customTo);

        $extraFilters = array_filter($request->only(['category', 'stage', 'rating', 'customer_id']));

        $current = $service->summarize(array_merge($extraFilters, [
            'date_from' => $range['date_from'],
            'date_to' => $range['date_to'],
        ]));

        $previous = $service->summarize(array_merge($extraFilters, [
            'date_from' => $range['prev_date_from'],
            'date_to' => $range['prev_date_to'],
        ]));

        $growth = $service->growth($current, $previous);

        $categoryChartUrl = $this->quickChartUrl([
            'type' => 'bar',
            'data' => [
                'labels' => $current['byCategory']->pluck('label')->toArray(),
                'datasets' => [[
                    'label' => 'Jumlah Opty',
                    'data' => $current['byCategory']->pluck('count')->toArray(),
                    'backgroundColor' => '#2AA9E0',
                ]],
            ],
            'options' => ['plugins' => ['legend' => ['display' => false]]],
        ]);

        $stageChartUrl = $this->quickChartUrl([
            'type' => 'pie',
            'data' => [
                'labels' => $current['byStage']->pluck('label')->toArray(),
                'datasets' => [[
                    'data' => $current['byStage']->pluck('count')->toArray(),
                    'backgroundColor' => ['#19A9DB', '#F6B01A', '#16A34A', '#DC2626'],
                ]],
            ],
        ]);

        $pdf = Pdf::loadView('pdf.report', [
            'filters' => $extraFilters,
            'range' => $range,
            'opportunities' => $current['rows'],
            'totalCount' => $current['totalCount'],
            'totalTcv' => $current['totalTcv'],
            'totalGpNominal' => $current['totalGpNominal'],
            'wonTcv' => $current['wonTcv'],
            'growth' => $growth,
            'byCategory' => $current['byCategory'],
            'categoryChartUrl' => $categoryChartUrl,
            'stageChartUrl' => $stageChartUrl,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $safeLabel = preg_replace('/[^A-Za-z0-9\-]+/', '-', $range['label']);

        return $pdf->download('opty-report-'.$safeLabel.'-'.now()->format('His').'.pdf');
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
