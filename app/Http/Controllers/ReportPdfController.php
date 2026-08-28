<?php

namespace App\Http\Controllers;

use App\Services\OpportunityReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        // Khusus buat PDF: 4 grafik (bukan 2), soalnya PDF itu dokumen statis
        // yang gak bisa di-toggle kayak di web/PWA — jadi Jumlah Opty DAN
        // Nilai TCV dua-duanya langsung ditampilin sekaligus per kategori
        // maupun per stage. Tiap bar/slice juga dikasih angka aslinya
        // langsung (data label), gak cuma ngandelin warna doang.
        $categoryCountChartUrl = $this->quickChartUrl($this->categoryChartConfig($current['byCategory'], 'count'));
        $categoryTcvChartUrl = $this->quickChartUrl($this->categoryChartConfig($current['byCategory'], 'tcv'));
        $stageCountChartUrl = $this->quickChartUrl($this->stageChartConfig($current['byStage'], 'count'));
        $stageTcvChartUrl = $this->quickChartUrl($this->stageChartConfig($current['byStage'], 'tcv'));

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
            'categoryCountChartUrl' => $categoryCountChartUrl,
            'categoryTcvChartUrl' => $categoryTcvChartUrl,
            'stageCountChartUrl' => $stageCountChartUrl,
            'stageTcvChartUrl' => $stageTcvChartUrl,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $safeLabel = preg_replace('/[^A-Za-z0-9\-]+/', '-', $range['label']);

        return $pdf->download('opty-report-'.$safeLabel.'-'.now()->format('His').'.pdf');
    }

    /**
     * Config chart bar "per Kategori", bisa metric 'count' (jumlah opty)
     * atau 'tcv' (nilai Rp). Datalabels dinyalain biar angkanya nempel
     * langsung di tiap bar.
     */
    private function categoryChartConfig(Collection $rows, string $metric): array
    {
        $isTcv = $metric === 'tcv';

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $rows->pluck('label')->toArray(),
                'datasets' => [[
                    'label' => $isTcv ? 'Total TCV (Rp)' : 'Jumlah Opty',
                    'data' => $isTcv ? $rows->pluck('tcv')->toArray() : $rows->pluck('count')->toArray(),
                    'backgroundColor' => '#2AA9E0',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'anchor' => 'end',
                        'align' => 'top',
                        'color' => '#131B33',
                        'font' => ['weight' => 'bold', 'size' => 9],
                        'formatter' => $isTcv
                            ? "function(v){ return 'Rp ' + Math.round(v/1000000).toLocaleString('id-ID') + 'jt'; }"
                            : "function(v){ return v; }",
                    ],
                ],
            ],
        ];
    }

    /**
     * Config chart pie "per Stage", sama polanya kayak categoryChartConfig
     * di atas — bedanya tipe chart-nya pie, warnanya ngikutin warna stage.
     */
    private function stageChartConfig(Collection $rows, string $metric): array
    {
        $isTcv = $metric === 'tcv';

        return [
            'type' => 'pie',
            'data' => [
                'labels' => $rows->pluck('label')->toArray(),
                'datasets' => [[
                    'data' => $isTcv ? $rows->pluck('tcv')->toArray() : $rows->pluck('count')->toArray(),
                    'backgroundColor' => ['#19A9DB', '#F6B01A', '#16A34A', '#DC2626'],
                ]],
            ],
            'options' => [
                'plugins' => [
                    'datalabels' => [
                        'color' => '#fff',
                        'font' => ['weight' => 'bold', 'size' => 9],
                        'formatter' => $isTcv
                            ? "function(v){ return 'Rp ' + Math.round(v/1000000).toLocaleString('id-ID') + 'jt'; }"
                            : "function(v){ return v; }",
                    ],
                ],
            ],
        ];
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
