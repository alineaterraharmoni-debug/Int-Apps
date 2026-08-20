<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // firstOrCreate di UserSeeder sengaja gak nimpa akun yang udah ada
        // (biar gak reset password orang), tapi efek sampingnya: akun Teddy
        // yang dibuat SEBELUM kolom is_admin ada gak pernah ke-flag admin.
        // Fix ini langsung UPDATE baris-nya, gak lewat seeder.
        DB::table('users')
            ->where('email', 'teddy@alineaterra.com')
            ->update(['is_admin' => true, 'role_id' => null]);
    }

    public function down(): void
    {
        // Sengaja gak di-revert — jangan sampai kepencet rollback gak sengaja
        // terus Super Admin ke-lockout dari app-nya sendiri.
    }
};
