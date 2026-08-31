<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya "description" nyampur 2 fungsi: baris pertama jadi nama
     * item (ditampilin bold di PDF via split newline), sisanya jadi
     * deskripsi tambahan. Sekarang dipisah jadi 2 field beneran:
     * - item_name  : nama item, WAJIB, ditampilin BOLD di PDF.
     * - description: deskripsi tambahan, OPSIONAL, gak ditampilin di PDF
     *                kalau kosong.
     * Data lama (item_name null) tetep aman — PDF fallback ke description
     * kalau item_name belum keisi (lihat Document model / view PDF).
     */
    public function up(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            if (! Schema::hasColumn('document_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('product_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
    }
};
