<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya Customer cuma bisa punya 1 PIC (pic_name/pic_phone/pic_email
     * single field). Sekarang bisa lebih dari satu — polanya sama persis
     * kayak `contacts` di Vendor (array of {name, position, phone, email}).
     * Kolom lama (pic_name/pic_phone/pic_email) SENGAJA dibiarin apa adanya,
     * gak dihapus, cuma gak dipake lagi di form baru.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'pics')) {
                $table->json('pics')->nullable()->after('pic_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('pics');
        });
    }
};