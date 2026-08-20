<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateAccount extends Component
{
    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('nullable|exists:roles,id')]
    public ?int $role_id = null;

    public ?string $revealedPassword = null;
    public ?string $revealedFor = null;

    public function render()
    {
        return view('livewire.create-account', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function save(): void
    {
        $data = $this->validate();

        // Delegated creator TIDAK bisa bikin sesama Super Admin — cuma
        // Super Admin asli yang bisa nge-grant status itu (lewat Kelola Akun).
        $password = Str::password(12);
        $user = User::create([...$data, 'password' => $password, 'is_admin' => false]);

        $this->revealedPassword = $password;
        $this->revealedFor = $user->email;
        $this->reset(['name', 'email', 'role_id']);
        $this->resetErrorBag();
    }
}
