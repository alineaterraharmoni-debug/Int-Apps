<?php

namespace App\Services;

use App\Models\Opportunity;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OpportunityReportService
{
    /**
     * Resolve a date range + its "previous period" counterpart (buat growth comparison)
     * dari pilihan period (monthly/quarterly/yearly) + parameter tahun/bulan/kuartal.
     */
    public function resolvePeriod(string $period, int $year, ?int $month = null, ?int $quarter = null): array
    {
        $month = $month ?: now()->month;
        $quarter = $quarter ?: (int) ceil(now()->month / 3);

        switch ($period) {
            case 'monthly':
                $start = Carbon::create($year, $month, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $label = $start->translatedFormat('F Y');
                break;

            case 'quarterly':
                $startMonth = (($quarter - 1) * 3) + 1;
                $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
                $end = $start->copy()->addMonths(2)->endOfMonth();
                $prevStart = $start->copy()->subMonths(3);
                $prevEnd = $prevStart->copy()->addMonths(2)->endOfMonth();
                $label = "Q{$quarter} {$year}";
                break;

            default: // yearly
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = $start->copy()->endOfYear();
                $prevStart = $start->copy()->subYear();
                $prevEnd = $prevStart->copy()->endOfYear();
                $label = (string) $year;
                break;
        }

        return [
            'date_from' => $start->toDateString(),
            'date_to' => $end->toDateString(),
            'prev_date_from' => $prevStart->toDateString(),
            'prev_date_to' => $prevEnd->toDateString(),
            'label' => $label,
        ];
    }

    /**
     * Ringkasan angka (total number, TCV, GP) + breakdown per kategori/stage untuk satu rentang filter.
     */
    public function summarize(array $filters): array
    {
        $rows = Opportunity::query()->filter($filters)->with('customer')->get();

        return [
            'rows' => $rows,
            'totalCount' => $rows->count(),
            'totalTcv' => (float) $rows->sum('tcv'),
            'totalGpNominal' => (float) $rows->sum(fn ($o) => $o->gp_nominal),
            'wonCount' => $rows->where('stage', 'won')->count(),
            'wonTcv' => (float) $rows->where('stage', 'won')->sum('tcv'),
            'byCategory' => $this->groupBy($rows, 'category', Opportunity::CATEGORIES),
            'byStage' => $this->groupBy($rows, 'stage', Opportunity::STAGES),
        ];
    }

    /**
     * Bandingin summary periode ini vs periode sebelumnya buat growth indicator (%).
     */
    public function growth(array $current, array $previous): array
    {
        $pct = function (float $now, float $prev): ?float {
            if ($prev == 0.0) {
                return $now > 0 ? 100.0 : null;
            }

            return round((($now - $prev) / $prev) * 100, 1);
        };

        return [
            'count' => $pct($current['totalCount'], $previous['totalCount']),
            'tcv' => $pct($current['totalTcv'], $previous['totalTcv']),
            'gp' => $pct($current['totalGpNominal'], $previous['totalGpNominal']),
            'won_tcv' => $pct($current['wonTcv'], $previous['wonTcv']),
        ];
    }

    private function groupBy(Collection $rows, string $field, array $labels): Collection
    {
        return $rows->groupBy($field)->map(fn ($group, $key) => [
            'label' => $labels[$key] ?? $key,
            'count' => $group->count(),
            'tcv' => (float) $group->sum('tcv'),
        ])->values();
    }
}
