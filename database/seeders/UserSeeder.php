<?php

namespace Database\Seeders;

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
        // balik password yang udah diganti user atau status admin yang diubah.
        $accounts = [
            ['name' => 'Teddy Syach', 'email' => 'teddy@alineaterra.com', 'password' => 'szVK6gPloee0', 'is_admin' => true],
            ['name' => 'Ari Setiawan', 'email' => 'ari@alineaterra.com', 'password' => '6aFKuarx7YxC', 'is_admin' => false],
            ['name' => 'Hanif Nuryanto', 'email' => 'hanif@alineaterra.com', 'password' => 'I9rURyCp2ARW', 'is_admin' => false],
            ['name' => 'Risky Leonardo', 'email' => 'risky@alineaterra.com', 'password' => 's5ov9bxkEQZ3', 'is_admin' => false],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'is_admin' => $account['is_admin'],
                ]
            );
        }
    }
}
