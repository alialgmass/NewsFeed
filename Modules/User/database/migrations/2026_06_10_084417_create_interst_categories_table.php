<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interst_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_category_id')->constrained('new_categories');
            $table->foreignId('user_id')->constrained('users');
            $table->double('level')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interst_categories');
    }
};
