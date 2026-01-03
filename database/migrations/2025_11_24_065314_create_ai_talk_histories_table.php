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
            $table->text('message');
            $table->string('emotion_data')->nullable();
            $table->integer('select_speaker')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('ai_agent_id');
            $table->foreignId('ai_talk_session_id');
            $table->foreignId('task_id')->nullable();
            $table->foreignId('category_id')->nullable();
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
