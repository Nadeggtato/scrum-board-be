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
        Schema::create('user_stories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('description');
            $table->integer('story_points')->nullable();
            $table->uuid('sprint_id')->nullable();
            $table->timestamps();

            $table->foreign('sprint_id')->references('id')->on('sprints')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stories');
    }
};
