<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `type` nyimpen lini produk apa aja yang vendor ini kuasain — pake
     * KEY YANG SAMA PERSIS kayak Opportunity::CATEGORIES (bukan taksonomi
     * baru yang beda sendiri), biar bisa langsung dikorelasiin: opty
     * kategori Cybersecurity -> otomatis bisa disaranin vendor mana yang
     * relevan buat "Cari harga dari Disti/Vendor" di checklist Leads.
     * Array (JSON) soalnya satu vendor bisa bawa lebih dari satu lini.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'type')) {
                $table->json('type')->nullable()->after('name');
            }
            if (! Schema::hasColumn('vendors', 'phone')) {
                $table->string('phone')->nullable()->after('contact_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['type', 'phone']);
        });
    }
};
