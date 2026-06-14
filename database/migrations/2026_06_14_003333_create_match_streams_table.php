<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_streams', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fixture_id')->unique();
            $table->string('stream_url', 1000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('fixture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_streams');
    }
};
