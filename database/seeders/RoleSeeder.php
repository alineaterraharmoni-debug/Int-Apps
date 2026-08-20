<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'permissions' => ['crm.view', 'crm.manage_mql_only', 'customer.view'],
            ],
            [
                'name' => 'Presales',
                'slug' => 'presales',
                'permissions' => ['crm.view', 'crm.manage', 'customer.view', 'customer.manage', 'document.view', 'document.manage'],
            ],
            [
                'name' => 'Engineer',
                'slug' => 'engineer',
                'permissions' => ['report.view', 'ticketing.view'],
            ],
            [
                'name' => 'Read-only',
                'slug' => 'read-only',
                'permissions' => ['crm.view', 'report.view', 'customer.view', 'team.view'],
            ],
        ];

        foreach ($roles as $role) {
            // firstOrCreate — jangan pernah nimpa balik role yang udah diedit admin.
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name'], 'permissions' => $role['permissions'], 'is_system' => false]
            );
        }
    }
}
