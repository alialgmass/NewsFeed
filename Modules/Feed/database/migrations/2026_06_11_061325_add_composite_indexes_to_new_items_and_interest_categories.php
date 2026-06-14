<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->index(['new_category_id', 'published_at'], 'idx_news_items_category_published');
        });

        Schema::table('interest_categories', function (Blueprint $table) {
            $table->unique(['user_id', 'new_category_id'], 'idx_interests_user_category_unique');
            $table->index(['user_id', 'new_category_id', 'level'], 'idx_interests_user_category_level');
        });
    }

    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropIndex('idx_news_items_category_published');
        });

        Schema::table('interest_categories', function (Blueprint $table) {
            $table->dropIndex('idx_interests_user_category_unique');
            $table->dropIndex('idx_interests_user_category_level');
        });
    }
};
