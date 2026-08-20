<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'presales')->first();

        if (! $role) {
            return;
        }

        $permissions = json_decode($role->permissions, true) ?? [];
        $permissions = array_unique(array_merge($permissions, ['document.view', 'document.manage']));

        DB::table('roles')->where('id', $role->id)->update([
            'permissions' => json_encode(array_values($permissions)),
        ]);
    }

    public function down(): void
    {
        //
    }
};
