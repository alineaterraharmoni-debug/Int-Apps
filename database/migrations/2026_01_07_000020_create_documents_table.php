<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['quotation', 'invoice', 'po', 'bast']);
            $table->string('number')->unique(); // cth: 006-QUO/AD/05/26
            $table->date('doc_date');

            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();

            // Quotation/Invoice/BAST -> ditujukan ke customer.
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // PO -> ditujukan ke vendor/distributor.
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->string('contact_name')->nullable();

            // Nomor referensi silang antar dokumen (quotation/PO/invoice) — bisa
            // nunjuk ke dokumen lain di sistem ini, atau diketik manual kalau
            // dokumen sumbernya lama/di luar sistem.
            $table->string('ref_quotation_number')->nullable();
            $table->string('ref_po_number')->nullable();
            $table->string('ref_invoice_number')->nullable();

            $table->text('terms')->nullable();
            $table->string('signatory_name')->default('Teddy Syach');
            $table->decimal('total', 15, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('group_label')->nullable(); // cth: "Option 2 - TrendAI Essentials"
            $table->string('product_type')->nullable(); // khusus PO: "TrendAI Flex (credits)"
            $table->text('description');
            $table->decimal('qty', 10, 2)->default(1);
            $table->string('unit')->nullable(); // "Lot", "Unit", dst
            $table->integer('credits_required')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_items');
        Schema::dropIfExists('documents');
    }
};
