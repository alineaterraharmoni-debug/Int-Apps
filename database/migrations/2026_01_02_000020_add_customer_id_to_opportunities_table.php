<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            // customer_name (string) dipertahankan sebagai cache nama buat tampilan cepat,
            // tapi sumber kebenaran sekarang ada di customers.id lewat customer_id.
            $table->foreignId('customer_id')->nullable()->after('customer_name')
                ->constrained('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
