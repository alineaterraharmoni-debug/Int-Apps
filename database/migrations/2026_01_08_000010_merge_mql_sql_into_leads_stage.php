<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perluas dulu enum-nya biar 'leads' jadi value yang VALID
        // (sementara masih ada mql/sql juga) — baru boleh nulis data ke situ.
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('mql','sql','leads','develop','won','lost') NOT NULL DEFAULT 'mql'");

        // 2. Baru pindahin data yang ada.
        DB::table('opportunities')
            ->whereIn('stage', ['mql', 'sql'])
            ->update(['stage' => 'leads']);

        // 3. Terakhir baru persempit enum-nya ke daftar final (mql/sql udah gak ada yang pakai).
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('leads','develop','won','lost') NOT NULL DEFAULT 'leads'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('mql','sql','leads','develop','won','lost') NOT NULL DEFAULT 'mql'");
        DB::table('opportunities')->where('stage', 'leads')->update(['stage' => 'mql']);
        DB::statement("ALTER TABLE opportunities MODIFY COLUMN stage ENUM('mql','sql','develop','won','lost') NOT NULL DEFAULT 'mql'");
    }
};
