<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pajak (PPN/PPh/lain-lain) khusus buat dokumen Invoice — bisa lebih
     * dari satu baris pajak per dokumen (misal PPN 11% + PPh 23 2%),
     * masing-masing bisa persentase dari subtotal ATAU nominal tetap.
     */
    public function up(): void
    {
        Schema::create('document_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // cth: "PPN 11%", "PPh 23"
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2); // angka persen ATAU nominal, tergantung type
            $table->decimal('amount', 15, 2)->default(0); // hasil akhir dalam Rupiah (snapshot pas disimpen)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_taxes');
    }
};
