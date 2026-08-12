<?php

namespace App\Livewire;

use App\Models\Opportunity;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportDashboard extends Component
{
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $category = null;
    public ?string $stage = null;
    public ?string $rating = null;

    public function updated(): void
    {
        // Livewire otomatis re-render tiap filter berubah
    }

    public function resetFilters(): void
    {
        $this->reset(['date_from', 'date_to', 'category', 'stage', 'rating']);
    }

    protected function filters(): array
    {
        return [
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'category' => $this->category,
            'stage' => $this->stage,
            'rating' => $this->rating,
        ];
    }

    public function render()
    {
        $base = Opportunity::query()->filter($this->filters());

        $totalCount = (clone $base)->count();
        $totalTcv = (clone $base)->sum('tcv');
        $totalGpNominal = (clone $base)->get()->sum(fn ($o) => $o->gp_nominal);

        $byCategory = (clone $base)->get()
            ->groupBy('category')
            ->map(fn ($rows, $key) => [
                'label' => Opportunity::CATEGORIES[$key] ?? $key,
                'count' => $rows->count(),
                'tcv' => $rows->sum('tcv'),
            ]);

        $byStage = (clone $base)->get()
            ->groupBy('stage')
            ->map(fn ($rows, $key) => [
                'label' => Opportunity::STAGES[$key] ?? $key,
                'count' => $rows->count(),
                'tcv' => $rows->sum('tcv'),
            ]);

        $byRating = (clone $base)->get()
            ->groupBy('rating')
            ->map(fn ($rows, $key) => [
                'label' => Opportunity::RATINGS[$key] ?? $key,
                'count' => $rows->count(),
            ]);

        return view('livewire.report-dashboard', [
            'categories' => Opportunity::CATEGORIES,
            'stages' => Opportunity::STAGES,
            'ratings' => Opportunity::RATINGS,
            'totalCount' => $totalCount,
            'totalTcv' => $totalTcv,
            'totalGpNominal' => $totalGpNominal,
            'byCategory' => $byCategory,
            'byStage' => $byStage,
            'byRating' => $byRating,
        ]);
    }
}
