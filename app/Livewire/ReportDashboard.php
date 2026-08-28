<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Services\OpportunityReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportDashboard extends Component
{
    public string $period = 'monthly'; // monthly | quarterly | yearly | custom
    public int $year;
    public int $month;
    public int $quarter;
    public ?string $custom_from = null;
    public ?string $custom_to = null;

    public ?string $category = null;
    public ?string $stage = null;
    public ?string $rating = null;
    public ?string $customer_id = null;
    public bool $showFilters = false;

    public string $chartMetric = 'count'; // count | tcv

    public int $detailPage = 1;
    const DETAIL_PER_PAGE = 25;

    public function mount(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
        $this->quarter = (int) ceil(now()->month / 3);
        $this->custom_from = now()->startOfMonth()->toDateString();
        $this->custom_to = now()->toDateString();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function setChartMetric(string $metric): void
    {
        $this->chartMetric = in_array($metric, ['count', 'tcv'], true) ? $metric : 'count';
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'stage', 'rating', 'customer_id']);
    }

    public function goToDetailPage(int $page): void
    {
        $this->detailPage = max(1, $page);
    }

    // Livewire 3 manggil ini otomatis tiap kali ADA property publik yang
    // berubah dari frontend (termasuk lewat wire:model.live). Dipakai buat
    // trigger re-init Chart.js, soalnya Livewire gak nge-dispatch event
    // DOM 'livewire:updated' kayak versi 2 dulu.
    public function updated($name, $value = null): void
    {
        // Ganti periode/filter apapun -> balikin ke halaman 1 Detail Opty,
        // biar gak nyangkut di halaman kosong kalau hasil filter baru lebih
        // dikit. Sengaja di-whitelist (bukan "semua kecuali detailPage")
        // biar toggle chart/filter panel gak ikut ke-reset tanpa alasan.
        $dataAffecting = ['period', 'year', 'month', 'quarter', 'custom_from', 'custom_to', 'category', 'stage', 'rating', 'customer_id'];
        if (in_array($name, $dataAffecting, true)) {
            $this->detailPage = 1;
        }

        $this->dispatch('report-updated');
    }

    public function render(OpportunityReportService $service)
    {
        $range = $service->resolvePeriod($this->period, $this->year, $this->month, $this->quarter, $this->custom_from, $this->custom_to);

        $extraFilters = array_filter([
            'category' => $this->category,
            'stage' => $this->stage,
            'rating' => $this->rating,
            'customer_id' => $this->customer_id,
        ]);

        $current = $service->summarize(array_merge($extraFilters, [
            'date_from' => $range['date_from'],
            'date_to' => $range['date_to'],
        ]));

        $previous = $service->summarize(array_merge($extraFilters, [
            'date_from' => $range['prev_date_from'],
            'date_to' => $range['prev_date_to'],
        ]));

        $growth = $service->growth($current, $previous);

        // Detail Opty di-paginate MANUAL (bukan lewat query ->paginate())
        // soalnya summarize() butuh koleksi PENUH buat ngitung total/growth.
        // Yang di-slice cuma buat tampilan tabel/card-nya aja.
        $detailTotalPages = max(1, (int) ceil($current['rows']->count() / self::DETAIL_PER_PAGE));
        if ($this->detailPage > $detailTotalPages) {
            $this->detailPage = $detailTotalPages;
        }
        $detailRows = $current['rows']->forPage($this->detailPage, self::DETAIL_PER_PAGE)->values();

        // Jamin chart selalu di-refresh tiap render (filter apapun yang berubah).
        $this->dispatch('report-updated');

        return view('livewire.report-dashboard', [
            'categories' => Opportunity::CATEGORIES,
            'stages' => Opportunity::STAGES,
            'ratings' => Opportunity::RATINGS,
            'customers' => Customer::orderBy('name')->get(),
            'range' => $range,
            'current' => $current,
            'previous' => $previous,
            'growth' => $growth,
            'detailRows' => $detailRows,
            'detailTotalPages' => $detailTotalPages,
        ]);
    }
}
