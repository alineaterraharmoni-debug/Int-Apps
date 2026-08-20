<?php

namespace App\Livewire;

use App\Models\TeamMember;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class TeamMembers extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('array|min:1')]
    public array $roles = [];

    public bool $is_active = true;

    public function render()
    {
        return view('livewire.team-members', [
            'members' => TeamMember::withCount(['opportunitiesAsSales', 'opportunitiesAsPresales', 'opportunitiesAsEngineer'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $m = TeamMember::findOrFail($id);
        $this->editingId = $m->id;
        $this->name = $m->name;
        $this->roles = $m->roles ?? [];
        $this->is_active = $m->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            TeamMember::findOrFail($this->editingId)->update($data);
        } else {
            TeamMember::create($data);
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if (! $this->editingId) {
            return;
        }

        $m = TeamMember::withCount(['opportunitiesAsSales', 'opportunitiesAsPresales', 'opportunitiesAsEngineer'])
            ->findOrFail($this->editingId);

        $totalLinked = $m->opportunities_as_sales_count + $m->opportunities_as_presales_count + $m->opportunities_as_engineer_count;

        if ($totalLinked > 0) {
            $this->addError('name', 'Orang ini masih ke-assign di ' . $totalLinked . ' opty. Nonaktifkan aja daripada dihapus.');

            return;
        }

        $m->delete();
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'roles']);
        $this->is_active = true;
        $this->resetErrorBag();
    }
}
