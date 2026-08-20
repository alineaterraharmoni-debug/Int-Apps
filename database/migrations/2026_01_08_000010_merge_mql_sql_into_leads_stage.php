<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah data yang udah ada dulu (opty di stage mql/sql -> leads).
        DB::table('opportunities')
            ->whereIn('stage', ['mql', 'sql'])
            ->update(['stage' => 'leads']);

        // 2. Baru ubah definisi enum kolomnya. MySQL butuh raw ALTER TABLE
        // buat ubah daftar value enum (Schema::table biasa gak bisa).
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('leads','develop','won','lost') NOT NULL DEFAULT 'leads'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('mql','sql','develop','won','lost') NOT NULL DEFAULT 'mql'");
        DB::table('opportunities')->where('stage', 'leads')->update(['stage' => 'mql']);
    }
};
