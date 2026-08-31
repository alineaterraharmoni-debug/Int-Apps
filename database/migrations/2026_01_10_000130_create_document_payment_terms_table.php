<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema pembayaran khusus Invoice: Lunas (default, gak ada baris di sini
     * sama sekali), DP (Down Payment), atau Termin (cicilan beberapa tahap).
     * `payment_scheme` nyimpen pilihan skemanya di tabel documents, baris
     * detail tahapannya (DP 50%, Termin 1, Termin 2, dst) disimpen di sini.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'payment_scheme')) {
                $table->enum('payment_scheme', ['full', 'dp', 'termin'])->default('full')->after('total');
            }
        });

        Schema::create('document_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // cth: "DP 50%", "Termin 1", "Pelunasan"
            $table->decimal('percentage', 5, 2)->nullable(); // opsional, buat referensi tampilan aja
            $table->decimal('amount', 15, 2)->default(0); // nominal Rupiah tahap ini
            $table->date('due_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_payment_terms');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('payment_scheme');
        });
    }
};
