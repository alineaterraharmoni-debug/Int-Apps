<?php

namespace App\Livewire;

use App\Models\TeamMember;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TeamMembers extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRole = '';
    public string $filterStatus = '';
    public bool $showFilters = false;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('array|min:1')]
    public array $roles = [];

    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->reset(['search', 'filterRole', 'filterStatus']);
        $this->resetPage();
    }

    private function canManage(): bool
    {
        return auth()->user()->hasPermission('team.manage');
    }

    public function render()
    {
        $members = TeamMember::withCount(['opportunitiesAsSales', 'opportunitiesAsPresales', 'opportunitiesAsEngineer'])
            ->when($this->search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($this->filterRole, fn ($q, $v) => $q->withRole($v))
            ->when($this->filterStatus === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.team-members', [
            'members' => $members,
            'canManage' => $this->canManage(),
        ]);
    }

    public function openCreate(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat daftar tim, gak bisa nambah orang baru.');
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat daftar tim, gak bisa edit data.');
        }

        $m = TeamMember::findOrFail($id);
        $this->editingId = $m->id;
        $this->name = $m->name;
        $this->roles = $m->roles ?? [];
        $this->is_active = $m->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin nyimpen data tim.');
        }

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
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin hapus data tim.');
        }

        if (! $this->editingId) {
            return;
        }

        $m = TeamMember::withCount(['opportunitiesAsSales', 'opportunitiesAsPresales', 'opportunitiesAsEngineer'])
            ->findOrFail($this->editingId);

        $totalLinked = $m->opportunities_as_sales_count + $m->opportunities_as_presales_count + $m->opportunities_as_engineer_count;

        if ($totalLinked > 0) {
            $this->addError('name', 'Orang ini masih ke-assign di '.$totalLinked.' opty. Nonaktifkan aja daripada dihapus.');

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

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama',
            'roles' => 'Peran',
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'roles']);
        $this->is_active = true;
        $this->resetErrorBag();
    }
}
