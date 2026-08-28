<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AccountManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:150')]
    public string $name = '';

    public string $email = '';

    public bool $is_admin = false;
    public bool $is_active = true;

    public ?int $role_id = null;

    // Ditampilin sekali aja setelah create/reset password, lalu ilang begitu modal ditutup.
    public ?string $revealedPassword = null;
    public ?string $revealedFor = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.account-management', [
            'users' => User::with('role')
                ->when($this->search, fn ($q, $v) => $q->where(fn ($qq) => $qq
                    ->where('name', 'like', "%{$v}%")
                    ->orWhere('email', 'like', "%{$v}%")))
                ->orderBy('name')
                ->paginate(25),
            'roles' => Role::orderBy('name')->get(),
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
        $this->is_active = $u->is_active;
        $this->role_id = $u->role_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:150',
            'email' => $this->editingId
                ? 'required|email|unique:users,email,'.$this->editingId
                : 'required|email|unique:users,email',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        // Super Admin gak butuh role spesifik — dia lolos semua permission check.
        if ($data['is_admin']) {
            $data['role_id'] = null;
        }

        if ($this->editingId) {
            $target = User::findOrFail($this->editingId);

            // Cegah demote diri sendiri kalau dia admin terakhir — biar gak ke-lockout.
            if ($target->is_admin && ! $data['is_admin'] && User::where('is_admin', true)->count() <= 1) {
                $this->addError('is_admin', 'Gak bisa — ini satu-satunya akun super admin yang tersisa.');

                return;
            }

            // Cegah nonaktifin diri sendiri atau admin terakhir — sama alasannya kayak di atas.
            if (! $data['is_active']) {
                if ($target->id === auth()->id()) {
                    $this->addError('is_active', 'Gak bisa nonaktifin akun sendiri.');

                    return;
                }
                if ($target->is_admin && User::where('is_admin', true)->where('is_active', true)->count() <= 1) {
                    $this->addError('is_active', 'Gak bisa — ini satu-satunya akun super admin aktif yang tersisa.');

                    return;
                }
            }

            $wasActive = $target->is_active;
            $target->update($data);

            // Baru dinonaktifin barusan -> paksa keluar dari sesi manapun dia lagi login.
            if ($wasActive && ! $data['is_active']) {
                $this->invalidateSessionsFor($target->id);
            }

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

        // Password baru harus beneran "nendang" sesi lama seketika — bukan
        // nunggu sesi lama expired sendiri (default 120 menit).
        $this->invalidateSessionsFor($user->id);

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

        $this->invalidateSessionsFor($target->id);
        $target->delete();
        $this->closeModal();
    }

    /**
     * Hapus semua baris sesi aktif punya user tertentu dari tabel `sessions`
     * (driver session app ini 'database', jadi ini beneran ngefek langsung).
     * Dipanggil pas reset password, nonaktifin akun, atau hapus akun — tiga
     * aksi yang secara logika HARUS langsung nendang sesi lama, bukan nunggu
     * expired sendiri.
     */
    private function invalidateSessionsFor(int $userId): void
    {
        DB::table('sessions')->where('user_id', $userId)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'is_admin', 'role_id', 'revealedPassword', 'revealedFor']);
        $this->is_active = true;
        $this->resetErrorBag();
    }
}
