<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class ChangePassword extends Component
{
    #[Validate('required|string')]
    public string $current_password = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $new_password = '';

    public string $new_password_confirmation = '';

    public bool $success = false;

    public function render()
    {
        return view('livewire.change-password');
    }

    public function save(): void
    {
        $this->validate();

        if (! Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Password lama salah.');

            return;
        }

        Auth::user()->update(['password' => $this->new_password]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->success = true;
    }
}
