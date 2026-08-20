<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\Customer;
use App\Models\TeamMember;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class OpportunityBoard extends Component
{
    // ----- Tampilan Board vs List -----
    public string $viewMode = 'board'; // board | list
    public string $listFilterStage = '';
    public string $listFilterRating = '';

    // ----- Modal & form state -----
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    #[Validate('required|exists:customers,id')]
    public $customer_id = null;

    public bool $showQuickAddCustomer = false;
    #[Validate('required_if:showQuickAddCustomer,true|string|max:150')]
    public string $new_customer_name = '';

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

    #[Validate('required|in:leads,develop,won,lost')]
    public string $stage = 'leads';

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
        $opportunities = Opportunity::with(['customer', 'sales', 'presales', 'engineers'])
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('stage');

        $listItems = Opportunity::with(['customer'])
            ->when($this->listFilterStage, fn ($q, $v) => $q->where('stage', $v))
            ->when($this->listFilterRating, fn ($q, $v) => $q->where('rating', $v))
            ->orderByDesc('updated_at')
            ->get();

        $canCreateOrEdit = $this->canManageFull() || $this->canManageMqlOnly();

        $this->dispatch('board-updated');

        return view('livewire.opportunity-board', [
            'stages' => Opportunity::STAGES,
            'categories' => Opportunity::CATEGORIES,
            'ratings' => Opportunity::RATINGS,
            'grouped' => $opportunities,
            'listItems' => $listItems,
            'customerOptions' => Customer::orderBy('name')->get(),
            'salesOptions' => TeamMember::active()->withRole('sales')->get(),
            'presalesOptions' => TeamMember::active()->withRole('presales')->get(),
            'engineerOptions' => TeamMember::active()->withRole('engineer')->get(),
            'canManageFull' => $this->canManageFull(),
            'canManageMqlOnly' => $this->canManageMqlOnly(),
            'canCreateOrEdit' => $canCreateOrEdit,
        ]);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['board', 'list'], true) ? $mode : 'board';
    }

    private function canManageFull(): bool
    {
        return auth()->user()->hasPermission('crm.manage');
    }

    private function canManageMqlOnly(): bool
    {
        return auth()->user()->hasPermission('crm.manage_mql_only');
    }

    public function openCreate(?string $stage = null): void
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo cuma bisa lihat pipeline ini, gak bisa nambah opty.');
        }

        $this->resetForm();
        if ($stage) {
            $this->stage = $stage;
        }
        // Role terbatas cuma boleh bikin opty di stage Leads.
        if (! $this->canManageFull() && $this->canManageMqlOnly()) {
            $this->stage = 'leads';
        }
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo cuma bisa lihat pipeline ini, gak bisa edit opty.');
        }

        $opty = Opportunity::with('engineers')->findOrFail($id);

        // Role terbatas Leads-only gak boleh megang opty yang udah lewat Leads.
        if (! $this->canManageFull() && $this->canManageMqlOnly() && $opty->stage !== 'leads') {
            abort(403, 'Opty ini udah lewat stage Leads — akun lo gak bisa edit lagi.');
        }

        $this->editingId = $opty->id;
        $this->title = $opty->title;
        $this->customer_id = $opty->customer_id;
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

    public function quickAddCustomer(): void
    {
        $this->validateOnly('new_customer_name', [
            'new_customer_name' => 'required|string|max:150',
        ]);

        $customer = Customer::create(['name' => $this->new_customer_name]);
        $this->customer_id = $customer->id;
        $this->new_customer_name = '';
        $this->showQuickAddCustomer = false;
    }

    public function save(): void
    {
        if (! $this->canManageFull() && ! $this->canManageMqlOnly()) {
            abort(403, 'Akun lo gak punya izin nyimpen opty.');
        }

        // Defense in depth: role Leads-only dipaksa stage-nya tetep 'leads' apapun
        // yang dikirim dari form (jaga-jaga kalau UI-nya ke-bypass).
        if (! $this->canManageFull() && $this->canManageMqlOnly()) {
            $this->stage = 'leads';
        }

        $data = $this->validate([
            'title' => 'required|string|max:150',
            'customer_id' => 'required|exists:customers,id',
            'category' => 'required|in:cybersecurity,cctv,data_center_networking,enterprise_networking,web_development,lainnya',
            'tcv' => 'required|numeric|min:0',
            'gp_percentage' => 'required|numeric|min:0|max:100',
            'rating' => 'required|in:high,med,low',
            'expected_closing_date' => 'nullable|date',
            'stage' => 'required|in:leads,develop,won,lost',
            'sales_id' => 'nullable|exists:team_members,id',
            'presales_id' => 'nullable|exists:team_members,id',
            'engineer_ids' => 'array',
            'next_action' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $engineerIds = $data['engineer_ids'] ?? [];
        unset($data['engineer_ids']);

        // customer_name dipertahankan sebagai cache tampilan cepat, disinkron dari master
        $data['customer_name'] = Customer::find($data['customer_id'])->name;

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
        if (! $this->canManageFull()) {
            abort(403, 'Cuma role dengan akses penuh yang bisa hapus opty.');
        }

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

        if (! $this->canManageFull()) {
            if (! $this->canManageMqlOnly()) {
                return; // gak punya izin sama sekali
            }
            // Role Leads-only cuma boleh geser opty yang lagi/mau ke Leads.
            $opty = Opportunity::find($id);
            if (! $opty || $opty->stage !== 'leads' || $stage !== 'leads') {
                return;
            }
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
            'editingId', 'title', 'customer_id', 'tcv', 'gp_percentage',
            'expected_closing_date', 'sales_id', 'presales_id', 'engineer_ids',
            'next_action', 'notes', 'showQuickAddCustomer', 'new_customer_name',
        ]);
        $this->category = 'cybersecurity';
        $this->rating = 'med';
        $this->stage = 'leads';
        $this->resetErrorBag();
    }
}
