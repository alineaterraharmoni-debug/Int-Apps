<?php

namespace App\Http\Controllers;

use App\Services\OpportunityReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    /**
     * Export "Detail Opty" hasil filter periode/kategori/dsb di Report ke
     * CSV (kebuka native di Excel/Sheets) — sama persis kayak
     * OpportunityExportController, gak nambah dependency Composer baru.
     */
    public function exportCsv(Request $request, OpportunityReportService $service): StreamedResponse
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

        $safeLabel = preg_replace('/[^A-Za-z0-9\-]+/', '-', $range['label']);
        $filename = 'report-opty-'.$safeLabel.'-'.now()->format('His').'.csv';

        return response()->streamDownload(function () use ($current) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 biar Excel baca karakter non-ASCII dengan bener.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Judul Opty', 'Customer', 'Kategori', 'Stage', 'Rating',
                'TCV (Rp)', 'GP (%)', 'GP Nominal (Rp)',
                'Sales', 'Presales', 'Ekspektasi Closing',
                'Alasan Menang', 'Alasan Kalah',
            ]);

            foreach ($current['rows'] as $o) {
                fputcsv($out, [
                    $o->title,
                    $o->customer?->name ?? $o->customer_name,
                    $o->category_label,
                    $o->stage_label,
                    $o->rating_label,
                    (int) $o->tcv,
                    rtrim(rtrim(number_format((float) $o->gp_percentage, 1, '.', ''), '0'), '.'),
                    (int) $o->gp_nominal,
                    $o->sales?->name ?? '',
                    $o->presales?->name ?? '',
                    optional($o->expected_closing_date)->format('Y-m-d') ?? '',
                    $o->won_category_label ?? '',
                    $o->lost_category_label ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
