<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('pic_email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_focus')->default(false); // ditandai sebagai focus customer
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_focus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
