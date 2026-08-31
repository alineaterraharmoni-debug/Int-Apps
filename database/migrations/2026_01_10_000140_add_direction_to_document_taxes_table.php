<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya SEMUA pajak digebyah-uyah nambahin ke Grand Total. Itu bener
     * buat PPN, tapi SALAH buat PPh (PPh 23, PPh Final, dst itu pajak yang
     * DIPOTONG dari yang customer bayar, bukan ditambahin) — makanya perlu
     * `direction` biar tiap baris pajak bisa milih nambah atau ngurangin.
     */
    public function up(): void
    {
        Schema::table('document_taxes', function (Blueprint $table) {
            if (! Schema::hasColumn('document_taxes', 'direction')) {
                $table->enum('direction', ['add', 'subtract'])->default('add')->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_taxes', function (Blueprint $table) {
            $table->dropColumn('direction');
        });
    }
};
