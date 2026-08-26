<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OpportunityExportController extends Controller
{
    /**
     * Export list pipeline opty ke CSV (kebuka otomatis di Excel/Sheets).
     *
     * Sengaja pake CSV native PHP (bukan library kayak Maatwebsite/Excel)
     * biar gak nambah dependency Composer baru — alur deploy Alinea gak
     * ada local dev buat jalanin `composer update` pas composer.lock perlu
     * disinkron ulang. fputcsv() udah cukup dan kebuka mulus di Excel.
     *
     * Kolom di export ini SENGAJA lebih lengkap dari tabel List di layar
     * (yang tetep 4 kolom biar ringkas di HP) — export jadi tempat buat
     * detail TCV, GP, sales, dan closing date yang gak muat di layar.
     */
    public function export(Request $request): StreamedResponse
    {
        $stage = $request->get('stage');
        $rating = $request->get('rating');

        $opportunities = Opportunity::with(['customer', 'sales', 'presales'])
            ->when($stage, fn ($q, $v) => $q->where('stage', $v))
            ->when($rating, fn ($q, $v) => $q->where('rating', $v))
            ->orderByDesc('updated_at')
            ->get();

        $filename = 'pipeline-opty-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($opportunities) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 biar Excel (terutama versi Windows) baca karakter
            // non-ASCII (mis. "·", nama dengan aksen) dengan bener, gak jadi kotak-kotak.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Judul Opty', 'Customer', 'Kategori', 'Stage', 'Rating',
                'TCV (Rp)', 'GP (%)', 'GP Nominal (Rp)',
                'Sales', 'Presales', 'Ekspektasi Closing', 'Tanggal Dibuat',
            ]);

            foreach ($opportunities as $o) {
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
                    $o->created_at->format('Y-m-d'),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
