<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Next Action sebelumnya teks bebas — sekarang jadi checklist tercentang
     * yang isinya nyesuain stage (lihat Opportunity::NEXT_ACTION_ITEMS).
     * Kolom lama `next_action` (string) SENGAJA dibiarin apa adanya, gak
     * dihapus, biar data lama gak hilang — cuma gak dipake lagi di form baru.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            if (! Schema::hasColumn('opportunities', 'next_action_checklist')) {
                $table->json('next_action_checklist')->nullable()->after('next_action');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('next_action_checklist');
        });
    }
};
