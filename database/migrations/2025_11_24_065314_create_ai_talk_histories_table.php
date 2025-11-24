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
        Schema::create('ai_talk_histories', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->string('emotion_data')->nullable();
            $table->integer('select_speaker')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('ai_agent_id');
            $table->foreignId('task_id');
            $table->foreignId('category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_talk_histories');
    }
};
