<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `phones` & `emails` jadi array (JSON) — satu vendor bisa punya lebih
     * dari satu nomor/email kontak. Kolom lama `phone` (string tunggal)
     * SENGAJA dibiarin apa adanya, gak dipake lagi di form baru.
     * `product_detail` buat catetan produk/brand apa aja yang dibawa vendor
     * ini — semuanya nullable, gak ada yang wajib.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'phones')) {
                $table->json('phones')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('vendors', 'emails')) {
                $table->json('emails')->nullable()->after('phones');
            }
            if (! Schema::hasColumn('vendors', 'product_detail')) {
                $table->text('product_detail')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['phones', 'emails', 'product_detail']);
        });
    }
};
