<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Teddy Syach', 'roles' => ['sales', 'presales']],
            ['name' => 'Ari Setiawan', 'roles' => ['sales', 'engineer']],
            ['name' => 'Hanif Nuryanto', 'roles' => ['presales', 'engineer']],
            ['name' => 'Risky Leonardo', 'roles' => ['sales', 'engineer']],
            ['name' => 'Freelancer Pool', 'roles' => ['sales']],
        ];

        foreach ($members as $member) {
            TeamMember::updateOrCreate(
                ['name' => $member['name']],
                ['roles' => $member['roles'], 'is_active' => true]
            );
        }
    }
}
