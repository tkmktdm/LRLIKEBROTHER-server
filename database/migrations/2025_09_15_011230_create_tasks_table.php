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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('notes')->nullable();
            $table->integer('status');
            $table->integer('score');
            $table->integer('sort_order');
            $table->integer('priority');
            $table->integer('start_date');
            $table->integer('end_date');
            $table->integer('target_date');
            $table->foreignId('user_id');
            // $table->foreignId('category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
