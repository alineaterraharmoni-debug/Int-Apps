<?php

namespace App\Livewire;

use App\Models\Role;
use App\Support\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class RoleManagement extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('array')]
    public array $selectedPermissions = [];

    public function render()
    {
        return view('livewire.role-management', [
            'roles' => Role::withCount('users')->orderBy('name')->get(),
            'catalog' => Permissions::CATALOG,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions ?? [];
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // crm.manage (penuh) dan crm.manage_mql_only saling eksklusif —
        // kalau dua-duanya kepilih, yang penuh yang menang, biar gak ambigu.
        $perms = $this->selectedPermissions;
        if (in_array('crm.manage', $perms, true)) {
            $perms = array_values(array_diff($perms, ['crm.manage_mql_only']));
        }

        $data = [
            'name' => $this->name,
            'permissions' => $perms,
        ];

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            if ($role->is_system) {
                $this->addError('name', 'Role bawaan sistem gak bisa diedit.');

                return;
            }
            $role->update($data);
        } else {
            $data['slug'] = \Illuminate\Support\Str::slug($this->name) . '-' . \Illuminate\Support\Str::random(4);
            Role::create($data);
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if (! $this->editingId) {
            return;
        }

        $role = Role::withCount('users')->findOrFail($this->editingId);

        if ($role->is_system) {
            $this->addError('name', 'Role bawaan sistem gak bisa dihapus.');

            return;
        }

        if ($role->users_count > 0) {
            $this->addError('name', 'Masih ada ' . $role->users_count . ' akun yang pakai role ini. Pindahin dulu role mereka.');

            return;
        }

        $role->delete();
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'selectedPermissions']);
        $this->resetErrorBag();
    }
}
