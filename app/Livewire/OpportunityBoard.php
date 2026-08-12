<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\TeamMember;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class OpportunityBoard extends Component
{
    // ----- Modal & form state -----
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    #[Validate('required|string|max:150')]
    public string $customer_name = '';

    #[Validate('required|in:cybersecurity,cctv,data_center_networking,enterprise_networking,web_development,lainnya')]
    public string $category = 'cybersecurity';

    #[Validate('required|numeric|min:0')]
    public $tcv = '';

    #[Validate('required|numeric|min:0|max:100')]
    public $gp_percentage = '';

    #[Validate('required|in:high,med,low')]
    public string $rating = 'med';

    #[Validate('nullable|date')]
    public ?string $expected_closing_date = null;

    #[Validate('required|in:mql,sql,develop,won,lost')]
    public string $stage = 'mql';

    #[Validate('nullable|exists:team_members,id')]
    public $sales_id = null;

    #[Validate('nullable|exists:team_members,id')]
    public $presales_id = null;

    #[Validate('array')]
    public array $engineer_ids = [];

    #[Validate('nullable|string|max:255')]
    public ?string $next_action = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    public function render()
    {
        $opportunities = Opportunity::with(['sales', 'presales', 'engineers'])
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('stage');

        return view('livewire.opportunity-board', [
            'stages' => Opportunity::STAGES,
            'categories' => Opportunity::CATEGORIES,
            'ratings' => Opportunity::RATINGS,
            'grouped' => $opportunities,
            'salesOptions' => TeamMember::active()->withRole('sales')->get(),
            'presalesOptions' => TeamMember::active()->withRole('presales')->get(),
            'engineerOptions' => TeamMember::active()->withRole('engineer')->get(),
        ]);
    }

    public function openCreate(?string $stage = null): void
    {
        $this->resetForm();
        if ($stage) {
            $this->stage = $stage;
        }
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $opty = Opportunity::with('engineers')->findOrFail($id);

        $this->editingId = $opty->id;
        $this->title = $opty->title;
        $this->customer_name = $opty->customer_name;
        $this->category = $opty->category;
        $this->tcv = (string) $opty->tcv;
        $this->gp_percentage = (string) $opty->gp_percentage;
        $this->rating = $opty->rating;
        $this->expected_closing_date = optional($opty->expected_closing_date)->format('Y-m-d');
        $this->stage = $opty->stage;
        $this->sales_id = $opty->sales_id;
        $this->presales_id = $opty->presales_id;
        $this->engineer_ids = $opty->engineers->pluck('id')->map(fn ($v) => (string) $v)->toArray();
        $this->next_action = $opty->next_action;
        $this->notes = $opty->notes;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $engineerIds = $data['engineer_ids'] ?? [];
        unset($data['engineer_ids']);

        if ($this->stage === 'won') {
            $data['closed_at'] = now()->toDateString();
        } elseif ($this->stage === 'lost') {
            $data['closed_at'] = now()->toDateString();
        } else {
            $data['closed_at'] = null;
        }

        if ($this->editingId) {
            $opty = Opportunity::findOrFail($this->editingId);
            $opty->update($data);
        } else {
            $opty = Opportunity::create($data);
        }

        $opty->engineers()->sync($engineerIds);

        $this->dispatch('opty-saved');
        $this->closeModal();
    }

    public function delete(): void
    {
        if ($this->editingId) {
            Opportunity::findOrFail($this->editingId)->delete();
        }
        $this->closeModal();
    }

    public function moveStage(int $id, string $stage): void
    {
        if (! array_key_exists($stage, Opportunity::STAGES)) {
            return;
        }

        $opty = Opportunity::findOrFail($id);
        $opty->stage = $stage;
        $opty->closed_at = in_array($stage, ['won', 'lost'], true) ? now()->toDateString() : null;
        $opty->save();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'customer_name', 'tcv', 'gp_percentage',
            'expected_closing_date', 'sales_id', 'presales_id', 'engineer_ids',
            'next_action', 'notes',
        ]);
        $this->category = 'cybersecurity';
        $this->rating = 'med';
        $this->stage = 'mql';
        $this->resetErrorBag();
    }
}
