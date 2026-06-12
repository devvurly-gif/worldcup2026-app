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
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image', 1000)->nullable();
            $table->string('source')->nullable();
            $table->string('url', 1000);
            $table->string('category')->default('general');
            $table->string('lang', 5)->default('fr');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['lang', 'category', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
