<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Dokumen berstatus Draft sekarang GAK punya nomor sama sekali (nomor
     * baru digenerate pas beneran difinalisasi) — jadi kolom ini harus boleh
     * NULL. Unique constraint yang udah ada tetep aman: MySQL gak nganggep
     * dua baris NULL sebagai "sama", jadi banyak draft bisa punya number=NULL
     * bareng tanpa nabrak unique index.
     *
     * Sengaja pake raw SQL (bukan ->change()) — ->change() butuh package
     * doctrine/dbal yang belum ke-install, dan nambah dependency baru bakal
     * bikin composer.lock out-of-sync (masalah yang sama kayak kenapa kita
     * gak pernah nambah package Composer baru tanpa alasan kuat).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE documents MODIFY number VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE documents MODIFY number VARCHAR(255) NOT NULL');
    }
};
