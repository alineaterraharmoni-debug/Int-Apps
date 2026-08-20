<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class AccountManagement extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    public string $email = '';

    public bool $is_admin = false;

    // Ditampilin sekali aja setelah create/reset password, lalu ilang begitu modal ditutup.
    public ?string $revealedPassword = null;
    public ?string $revealedFor = null;

    public function render()
    {
        return view('livewire.account-management', [
            'users' => User::orderBy('name')->get(),
            'adminCount' => User::where('is_admin', true)->count(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->is_admin = $u->is_admin;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:150',
            'email' => $this->editingId
                ? 'required|email|unique:users,email,' . $this->editingId
                : 'required|email|unique:users,email',
            'is_admin' => 'boolean',
        ]);

        if ($this->editingId) {
            // Cegah demote diri sendiri kalau dia admin terakhir — biar gak ke-lockout.
            $target = User::findOrFail($this->editingId);
            if ($target->is_admin && ! $data['is_admin'] && User::where('is_admin', true)->count() <= 1) {
                $this->addError('is_admin', 'Gak bisa — ini satu-satunya akun super admin yang tersisa.');

                return;
            }

            $target->update($data);
            $this->closeModal();
        } else {
            $password = Str::password(12);
            $user = User::create([...$data, 'password' => $password]);
            $this->revealedPassword = $password;
            $this->revealedFor = $user->email;
        }
    }

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);
        $password = Str::password(12);
        $user->update(['password' => $password]);

        $this->revealedPassword = $password;
        $this->revealedFor = $user->email;
        $this->showModal = true;
        $this->editingId = null;
    }

    public function delete(): void
    {
        if (! $this->editingId) {
            return;
        }

        if ($this->editingId === auth()->id()) {
            $this->addError('name', 'Gak bisa hapus akun sendiri.');

            return;
        }

        $target = User::findOrFail($this->editingId);
        if ($target->is_admin && User::where('is_admin', true)->count() <= 1) {
            $this->addError('name', 'Gak bisa — ini satu-satunya akun super admin yang tersisa.');

            return;
        }

        $target->delete();
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'is_admin', 'revealedPassword', 'revealedFor']);
        $this->resetErrorBag();
    }
}
