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

    public bool $showDetailModal = false;
    public ?int $detailId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('array')]
    public array $type = [];

    public ?string $product_detail = null;

    // Array of ['name'=>..., 'brand'=>..., 'phone'=>..., 'email'=>...] — satu
    // vendor bisa punya beberapa PIC, tiap orang pegang brand/produk beda.
    public array $contacts = [];

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

    public function addContact(): void
    {
        $this->contacts[] = ['name' => '', 'brand' => '', 'phone' => '', 'email' => ''];
    }

    public function removeContact(int $index): void
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);
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
            'detailVendor' => $this->detailId ? Vendor::find($this->detailId) : null,
        ]);
    }

    // Klik nama -> buka popup View Detail (read-only, siapapun boleh liat).
    // Edit sekarang dipindah jadi tombol DI DALEM popup ini.
    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function editFromDetail(int $id): void
    {
        $this->showDetailModal = false;
        $this->openEdit($id);
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
        $this->contacts = $v->contacts ?? [];
        $this->address = $v->address;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin nyimpen data vendor.');
        }

        // Semua field kontak (Lini Produk, Detail Produk, kontak, alamat)
        // SENGAJA gak ada yang wajib — cuma Nama Vendor yang wajib.
        $data = $this->validate([
            'name' => 'required|string|max:150',
            'type' => 'array',
            'product_detail' => 'nullable|string',
            'contacts' => 'array',
            'contacts.*.name' => 'nullable|string|max:100',
            'contacts.*.brand' => 'nullable|string|max:100',
            'contacts.*.phone' => 'nullable|string|max:50',
            'contacts.*.email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
        ], [], [
            'name' => 'Nama Vendor',
            'type' => 'Lini Produk',
            'contacts.*.email' => 'Email',
        ]);

        // Buang baris kontak yang kosong semua (nama, brand, telepon, email
        // gak diisi sama sekali) sebelum disimpen.
        $data['contacts'] = array_values(array_filter($this->contacts, function ($c) {
            return trim((string) ($c['name'] ?? '')) !== ''
                || trim((string) ($c['brand'] ?? '')) !== ''
                || trim((string) ($c['phone'] ?? '')) !== ''
                || trim((string) ($c['email'] ?? '')) !== '';
        }));

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
        $this->reset(['editingId', 'name', 'type', 'product_detail', 'contacts', 'address']);
        $this->resetErrorBag();
    }
}
