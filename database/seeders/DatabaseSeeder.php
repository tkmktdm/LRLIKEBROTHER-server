<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        \App\Models\Category::factory(5)->create();
        \App\Models\Category::create([
            'name' => 'Colors',
            'user_id' => '11',
        ]);
        \App\Models\Task::factory(5)->create();
        \App\Models\Task::create([
            'title' => '青色タスク',
            // 'note' => '',
            'category_id' => '6',
            'user_id' => '11',
        ]);
        \App\Models\AiAgent::create([
            'name' => 'gemini-2.5-flash',
            'version' => '2.5',
            'is_active' => '1',
            'user_id' => '11',
        ]);
        // \App\Models\Task::factory(5)->create([
        //     "title" => "タスクのタイトル",
        //     "notes" => "説明",
        //     "status" => 0,
        //     "user_id" => User::factory(),
        // ]);
    }
}
