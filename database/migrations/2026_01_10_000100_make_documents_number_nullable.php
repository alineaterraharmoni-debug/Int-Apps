<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * INI ROOT CAUSE error 500 pas Simpan Dokumen kalau ada item yang
     * Deskripsi-nya dikosongin: kolom `description` dari migration paling
     * awal itu NOT NULL, tapi form udah lama bikin field ini OPSIONAL
     * (waktu "Nama Item" dipisah dari "Deskripsi") — nyimpen NULL ke kolom
     * NOT NULL bikin MySQL nolak (constraint violation) -> Laravel ngelempar
     * QueryException yang gak ke-catch -> HTTP 500.
     *
     * Paling gampang kena pas Invoice narik item dari Quotation (fitur
     * auto-copy pas pilih Opty) — kalau item Quotation-nya ada yang
     * Deskripsinya kosong (emang udah boleh sekarang), ikut ke-copy kosong,
     * terus pas Invoice-nya disimpen baru ketauan gagal.
     *
     * Raw SQL (bukan ->change()) biar gak butuh doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE document_items MODIFY description TEXT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE document_items SET description = '' WHERE description IS NULL");
        DB::statement('ALTER TABLE document_items MODIFY description TEXT NOT NULL');
    }
};