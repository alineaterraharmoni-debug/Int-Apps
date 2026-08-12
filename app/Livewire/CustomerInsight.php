<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomerInsight extends Component
{
    public string $search = '';
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $sortBy = 'total_won'; // total_won | total_tcv | opty_count | name
    public bool $focusOnly = false;

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
    #[Validate('nullable|string')]
    public ?string $address = null;
    #[Validate('nullable|string')]
    public ?string $notes = null;

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
            'customers' => $customers->get(),
        ]);
    }

    public function toggleFocus(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_focus' => ! $customer->is_focus]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
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

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'industry', 'pic_name', 'pic_phone', 'pic_email', 'address', 'notes']);
        $this->resetErrorBag();
    }
}
