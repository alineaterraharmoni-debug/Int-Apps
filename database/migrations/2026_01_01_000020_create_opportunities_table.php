<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();

            // Detail utama
            $table->string('title');                 // Judul Opty
            $table->string('customer_name');          // Nama Customer
            $table->enum('category', [
                'cybersecurity',
                'cctv',
                'data_center_networking',
                'enterprise_networking',
                'web_development',
                'lainnya',
            ])->default('cybersecurity');

            // Nilai deal
            $table->decimal('tcv', 15, 2)->default(0);          // Estimasi TCV
            $table->decimal('gp_percentage', 5, 2)->default(0); // GP dalam persen dari TCV

            $table->enum('rating', ['high', 'med', 'low'])->default('med');
            $table->date('expected_closing_date')->nullable();  // Ekspektasi Closing

            // Pipeline
            $table->enum('stage', ['mql', 'sql', 'develop', 'won', 'lost'])->default('mql');
            $table->date('closed_at')->nullable();

            // Assignment
            $table->foreignId('sales_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->foreignId('presales_id')->nullable()->constrained('team_members')->nullOnDelete();

            $table->string('next_action')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['stage', 'category', 'rating']);
        });

        // Engineer yang di-assign saat opty Close WIN — bisa lebih dari satu orang
        Schema::create('opportunity_engineer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['opportunity_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_engineer');
        Schema::dropIfExists('opportunities');
    }
};
