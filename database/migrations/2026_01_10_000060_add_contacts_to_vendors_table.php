<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Redesign dari migration sebelumnya (phones/emails lepas) — ternyata yang
     * dibutuhin adalah kontak TERSTRUKTUR: satu vendor bisa punya beberapa
     * orang PIC, tiap orang pegang brand/produk yang beda (misal Mona pegang
     * Fortinet, Sinta pegang Kaspersky), masing-masing punya telepon & email
     * sendiri. Disimpen sebagai array of object:
     * [{"name":"Mona","brand":"Fortinet","phone":"...","email":"..."}, ...]
     * Kolom lama (`phone`, `phones`, `emails`, `contact_name`) SENGAJA
     * dibiarin apa adanya, gak dipake lagi di form baru.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'contacts')) {
                $table->json('contacts')->nullable()->after('contact_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('contacts');
        });
    }
};
