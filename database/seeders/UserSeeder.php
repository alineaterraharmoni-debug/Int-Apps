<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password di bawah ini di-generate random, satu-satu beda per orang.
        // WAJIB disebar via jalur aman (WA personal, bukan grup). Password bisa
        // diganti sendiri lewat menu "Ganti Password" setelah login.
        //
        // firstOrCreate — cuma isi kalau emailnya belum ada. Ini seeder auto-jalan
        // tiap deploy (Railway), jadi JANGAN pakai updateOrCreate biar gak nimpa
        // balik password/role yang udah diganti dari aplikasi.
        $accounts = [
            ['name' => 'Teddy Syach', 'email' => 'teddy@alineaterra.com', 'password' => 'szVK6gPloee0', 'is_admin' => true, 'role_slug' => null],
            ['name' => 'Ari Setiawan', 'email' => 'ari@alineaterra.com', 'password' => '6aFKuarx7YxC', 'is_admin' => false, 'role_slug' => 'presales'],
            ['name' => 'Hanif Nuryanto', 'email' => 'hanif@alineaterra.com', 'password' => 'I9rURyCp2ARW', 'is_admin' => false, 'role_slug' => 'presales'],
            ['name' => 'Risky Leonardo', 'email' => 'risky@alineaterra.com', 'password' => 's5ov9bxkEQZ3', 'is_admin' => false, 'role_slug' => 'sales'],
        ];

        foreach ($accounts as $account) {
            $roleId = $account['role_slug'] ? Role::where('slug', $account['role_slug'])->value('id') : null;

            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'is_admin' => $account['is_admin'],
                    'role_id' => $roleId,
                ]
            );
        }
    }
}
