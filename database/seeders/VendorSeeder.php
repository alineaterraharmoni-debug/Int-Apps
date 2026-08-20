<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            ['name' => 'PT. Virtus Technology Indonesia', 'contact_name' => 'Bpk. Fath Almashyur Muhammad', 'address' => 'Centennial Tower Jl. Gatot Subroto No.Kav. 24-25 lt. 39, Karet Semanggi, Kec. Setiabudi, Kota Jakarta Selatan, DKI Jakarta 12930'],
            ['name' => 'PT. Synnex Metrodata Indonesia', 'contact_name' => null, 'address' => null],
            ['name' => 'Executive Network (ENID)', 'contact_name' => null, 'address' => null],
        ];

        foreach ($vendors as $v) {
            Vendor::firstOrCreate(['name' => $v['name']], $v);
        }
    }
}
