<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CustomerInsight extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $sortBy = 'total_won'; // total_won | total_tcv | opty_count | name
    public bool $focusOnly = false;
    public bool $showFilters = false;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';
    #[Validate('nullable|string|max:100')]
    public ?string $industry = null;
    #[Validate('nullable|string|max:100')]
    public ?string $pic_name = null;
    #[Validate('nullable|string|max:50')]
    public ?string $pic_phone = null;
    #[Validate('nullable|email|max:150')]
    public ?string $pic_email = null;
    // Sebelumnya nullable — sekarang wajib, biar data alamat customer selalu
    // ada dari awal (dibutuhin misalnya buat pengiriman dokumen/kunjungan).
    #[Validate('required|string')]
    public ?string $address = null;
    #[Validate('nullable|string')]
    public ?string $notes = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function updatingFocusOnly(): void
    {
        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->reset(['date_from', 'date_to', 'focusOnly']);
        $this->sortBy = 'total_won';
        $this->resetPage();
    }

    private function canManage(): bool
    {
        return auth()->user()->hasPermission('customer.manage');
    }

    public function render()
    {
        $query = Customer::query()
            ->search($this->search)
            ->when($this->focusOnly, fn ($q) => $q->focus())
            ->withCount(['opportunities' => function ($q) {
                $q->when($this->date_from, fn ($qq, $v) => $qq->whereDate('created_at', '>=', $v))
                    ->when($this->date_to, fn ($qq, $v) => $qq->whereDate('created_at', '<=', $v));
            }])
            ->withSum(['opportunities as total_tcv' => function ($q) {
                $q->when($this->date_from, fn ($qq, $v) => $qq->whereDate('created_at', '>=', $v))
                    ->when($this->date_to, fn ($qq, $v) => $qq->whereDate('created_at', '<=', $v));
            }], 'tcv')
            ->withSum(['opportunities as total_won' => function ($q) {
                $q->where('stage', 'won')
                    ->when($this->date_from, fn ($qq, $v) => $qq->whereDate('closed_at', '>=', $v))
                    ->when($this->date_to, fn ($qq, $v) => $qq->whereDate('closed_at', '<=', $v));
            }], 'tcv')
            ->withMax(['opportunities as last_won_at' => fn ($q) => $q->where('stage', 'won')], 'closed_at');

        $customers = match ($this->sortBy) {
            'total_tcv' => $query->orderByDesc('total_tcv'),
            'opty_count' => $query->orderByDesc('opportunities_count'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('total_won'),
        };

        return view('livewire.customer-insight', [
            'customers' => $customers->paginate(25),
            'canManage' => $this->canManage(),
        ]);
    }

    public function toggleFocus(int $id): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat data customer, gak bisa ubah status Fokus.');
        }

        $customer = Customer::findOrFail($id);
        $customer->update(['is_focus' => ! $customer->is_focus]);
    }

    public function openCreate(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat data customer, gak bisa nambah data baru.');
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo cuma bisa lihat data customer, gak bisa edit data.');
        }

        $c = Customer::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->industry = $c->industry;
        $this->pic_name = $c->pic_name;
        $this->pic_phone = $c->pic_phone;
        $this->pic_email = $c->pic_email;
        $this->address = $c->address;
        $this->notes = $c->notes;
        $this->showModal = true;
    }

    public function save(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin nyimpen data customer.');
        }

        $data = $this->validate();

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
        } else {
            Customer::create($data);
        }

        $this->closeModal();
    }

    public function delete(): void
    {
        if (! $this->canManage()) {
            abort(403, 'Akun lo gak punya izin hapus data customer.');
        }

        if (! $this->editingId) {
            return;
        }

        $customer = Customer::withCount('opportunities')->findOrFail($this->editingId);

        if ($customer->opportunities_count > 0) {
            $this->addError('name', 'Customer ini masih punya opty terhubung, gak bisa dihapus.');

            return;
        }

        $customer->delete();
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
            'name' => 'Nama Customer',
            'address' => 'Alamat',
            'industry' => 'Industri',
            'pic_name' => 'Nama PIC',
            'pic_phone' => 'No. HP PIC',
            'pic_email' => 'Email PIC',
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'industry', 'pic_name', 'pic_phone', 'pic_email', 'address', 'notes']);
        $this->resetErrorBag();
    }
}
