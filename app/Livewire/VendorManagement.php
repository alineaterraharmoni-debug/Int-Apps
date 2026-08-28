<?php

namespace App\Livewire;

use App\Models\Opportunity;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class VendorManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public bool $showFilters = false;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('array')]
    public array $type = [];

    #[Validate('nullable|string|max:100')]
    public ?string $contact_name = null;
    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;
    #[Validate('nullable|string')]
    public ?string $address = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->reset(['search', 'filterType']);
        $this->resetPage();
    }

    private function canManage(): bool
    {
        return auth()->user()->hasPermission('vendor.manage');
    }

    public function render()
    {
        $vendors = Vendor::query()
            ->when($this->search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->forCategory($this->filterType ?: null)
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.vendor-management', [
            'vendors' => $vendors,
            'categories' => Opportunity::CATEGORIES,
            'canManage' => $this->canManage(),
        ]);
    }

    public function openCreate(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat daftar vendor, gak bisa nambah data baru.');
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat daftar vendor, gak bisa edit data.');
        }

        $v = Vendor::findOrFail($id);
        $this->editingId = $v->id;
        $this->name = $v->name;
        $this->type = $v->type ?? [];
        $this->contact_name = $v->contact_name;
        $this->phone = $v->phone;
        $this->address = $v->address;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin nyimpen data vendor.');
        }

        $data = $this->validate();

        if ($this->editingId) {
            Vendor::findOrFail($this->editingId)->update($data);
        } else {
            Vendor::create($data);
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin hapus data vendor.');
        }

        if (! $this->editingId) {
            return;
        }

        Vendor::findOrFail($this->editingId)->delete();
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
            'name' => 'Nama Vendor',
            'type' => 'Lini Produk',
            'contact_name' => 'Nama Kontak',
            'phone' => 'No. Telepon',
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'type', 'contact_name', 'phone', 'address']);
        $this->resetErrorBag();
    }
}
