<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'contact_title')) {
                $table->string('contact_title')->nullable()->after('contact_name');
            }
        });

        Schema::table('document_items', function (Blueprint $table) {
            if (! Schema::hasColumn('document_items', 'discount')) {
                $table->decimal('discount', 5, 2)->nullable()->after('qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('contact_title');
        });
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};