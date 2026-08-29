<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default level DATABASE sengaja 'final' (bukan 'draft') — biar dokumen
     * yang UDAH ADA di production (yang notabene emang udah jadi/dikirim)
     * gak keubah status jadi Draft pas kolom ini nongol. Form Livewire yang
     * nentuin dokumen BARU defaultnya 'draft' (di level PHP, bukan DB).
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'status')) {
                $table->enum('status', ['draft', 'final'])->default('final')->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
