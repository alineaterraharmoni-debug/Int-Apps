<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Services\OpportunityReportService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportDashboard extends Component
{
    public string $period = 'monthly'; // monthly | quarterly | yearly
    public int $year;
    public int $month;
    public int $quarter;

    public ?string $category = null;
    public ?string $stage = null;
    public ?string $rating = null;

    public function mount(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
        $this->quarter = (int) ceil(now()->month / 3);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'stage', 'rating']);
    }

    public function render(OpportunityReportService $service)
    {
        $range = $service->resolvePeriod($this->period, $this->year, $this->month, $this->quarter);

        $extraFilters = array_filter([
            'category' => $this->category,
            'stage' => $this->stage,
            'rating' => $this->rating,
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

        return view('livewire.report-dashboard', [
            'categories' => Opportunity::CATEGORIES,
            'stages' => Opportunity::STAGES,
            'ratings' => Opportunity::RATINGS,
            'range' => $range,
            'current' => $current,
            'previous' => $previous,
            'growth' => $growth,
        ]);
    }
}
