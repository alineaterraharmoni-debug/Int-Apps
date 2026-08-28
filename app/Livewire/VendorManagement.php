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

    public ?string $product_detail = null;
    public ?string $contact_name = null;

    // Array — satu vendor bisa punya lebih dari 1 no. telepon / email.
    // Semuanya opsional, boleh kosong sama sekali.
    public array $phones = [];
    public array $emails = [];

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

    public function addPhone(): void
    {
        $this->phones[] = '';
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function addEmail(): void
    {
        $this->emails[] = '';
    }

    public function removeEmail(int $index): void
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
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
        $this->product_detail = $v->product_detail;
        $this->contact_name = $v->contact_name;
        $this->phones = $v->phones ?? [];
        $this->emails = $v->emails ?? [];
        $this->address = $v->address;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin nyimpen data vendor.');
        }

        // Semua field kontak (Lini Produk, Detail Produk, telepon, email,
        // alamat) SENGAJA gak ada yang wajib — cuma Nama Vendor yang wajib.
        $data = $this->validate([
            'name' => 'required|string|max:150',
            'type' => 'array',
            'product_detail' => 'nullable|string',
            'contact_name' => 'nullable|string|max:100',
            'phones' => 'array',
            'phones.*' => 'nullable|string|max:50',
            'emails' => 'array',
            'emails.*' => 'nullable|email|max:150',
            'address' => 'nullable|string',
        ], [], [
            'name' => 'Nama Vendor',
            'type' => 'Lini Produk',
            'emails.*' => 'Email',
        ]);

        // Buang baris kosong (misal user klik "+ Tambah" tapi gak diisi) sebelum disimpen.
        $data['phones'] = array_values(array_filter($this->phones, fn ($p) => trim((string) $p) !== ''));
        $data['emails'] = array_values(array_filter($this->emails, fn ($e) => trim((string) $e) !== ''));

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

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'type', 'product_detail', 'contact_name', 'phones', 'emails', 'address']);
        $this->resetErrorBag();
    }
}
