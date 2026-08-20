<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password di bawah ini di-generate random, satu-satu beda per orang.
        // WAJIB disebar via jalur aman (WA personal, bukan grup) dan minta tiap
        // orang ganti password begitu ada fitur "ganti password" nanti.
        $accounts = [
            ['name' => 'Teddy Syach', 'email' => 'teddy@alineaterra.com', 'password' => 'szVK6gPloee0'],
            ['name' => 'Ari Setiawan', 'email' => 'ari@alineaterra.com', 'password' => '6aFKuarx7YxC'],
            ['name' => 'Hanif Nuryanto', 'email' => 'hanif@alineaterra.com', 'password' => 'I9rURyCp2ARW'],
            ['name' => 'Risky Leonardo', 'email' => 'risky@alineaterra.com', 'password' => 's5ov9bxkEQZ3'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => $account['password']]
            );
        }
    }
}
