<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indeks di kolom yang paling sering dipake buat WHERE/ORDER BY di
     * seluruh app (Board, List, Report, Customer Insight, dst). Ini investasi
     * "seringan mungkin ke depan" — sekarang datanya masih dikit jadi
     * dampaknya belum kerasa, tapi begitu opty/dokumen/customer numpuk
     * ratusan-ribuan baris, query yang nge-filter kolom ini bakal jauh lebih
     * cepet ketimbang full table scan. Aman 100% — indeks gak ngubah data
     * atau perilaku aplikasi sama sekali, cuma nge-percepat query.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'opportunities', 'stage');
            $this->addIndexIfMissing($table, 'opportunities', 'rating');
            $this->addIndexIfMissing($table, 'opportunities', 'category');
            $this->addIndexIfMissing($table, 'opportunities', 'expected_closing_date');
            $this->addIndexIfMissing($table, 'opportunities', 'updated_at');
        });

        Schema::table('documents', function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'documents', 'type');
            $this->addIndexIfMissing($table, 'documents', 'status');
            $this->addIndexIfMissing($table, 'documents', 'doc_date');
        });

        Schema::table('customers', function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'customers', 'name');
            $this->addIndexIfMissing($table, 'customers', 'is_focus');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'team_members', 'is_active');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex('opportunities_stage_index');
            $table->dropIndex('opportunities_rating_index');
            $table->dropIndex('opportunities_category_index');
            $table->dropIndex('opportunities_expected_closing_date_index');
            $table->dropIndex('opportunities_updated_at_index');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_type_index');
            $table->dropIndex('documents_status_index');
            $table->dropIndex('documents_doc_date_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_name_index');
            $table->dropIndex('customers_is_focus_index');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropIndex('team_members_is_active_index');
        });
    }

    private function addIndexIfMissing(Blueprint $table, string $tableName, string $column): void
    {
        $indexName = $tableName.'_'.$column.'_index';
        $exists = collect(Schema::getIndexes($tableName))->pluck('name')->contains($indexName);

        if (! $exists) {
            $table->index($column, $indexName);
        }
    }
};
