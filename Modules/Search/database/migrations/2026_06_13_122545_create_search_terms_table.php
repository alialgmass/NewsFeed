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
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 100)->unique();
            $table->unsignedInteger('frequency')->default(1);
            $table->timestamps();

            $table->index('frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_terms');
    }
};
