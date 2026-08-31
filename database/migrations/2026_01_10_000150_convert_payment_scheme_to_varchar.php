<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * INI ROOT CAUSE error 500 pas milih DP/Termin: migration sebelumnya
     * bikin `payment_scheme` sebagai ENUM('full','dp','termin') di level
     * database, tapi kode PHP-nya udah diubah nyimpen value 'staged'
     * (skema disederhanain jadi cuma 2 pilihan). MySQL nolak INSERT/UPDATE
     * value yang gak ada di daftar ENUM -> query exception -> 500.
     * Fix: kolomnya dilonggarin jadi VARCHAR biasa (gak perlu enum lagi),
     * pake raw SQL (bukan ->change()) biar gak butuh doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY payment_scheme VARCHAR(20) NOT NULL DEFAULT 'full'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documents MODIFY payment_scheme ENUM('full','dp','termin') NOT NULL DEFAULT 'full'");
    }
};
