<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simetris sama lost_category/lost_reason — sebelumnya cuma opty yang
     * kalah (Lost) yang ke-track alasannya, WON gak ada sama sekali.
     * Padahal buat evaluasi/replicate strategi, "kenapa kita menang" sama
     * pentingnya kayak "kenapa kita kalah".
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            if (! Schema::hasColumn('opportunities', 'won_category')) {
                $table->string('won_category')->nullable()->after('lost_reason');
            }
            if (! Schema::hasColumn('opportunities', 'won_reason')) {
                $table->text('won_reason')->nullable()->after('won_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn(['won_category', 'won_reason']);
        });
    }
};
