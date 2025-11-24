<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'notes',
        'status',
        'score',
        'sort_order',
        'priority',
        'start_date',
        'end_date',
        'target_date',
        'user_id',
        'category_id',
    ];

    protected $casts = [
        'title' => "string",
        'notes' => "string",
        'status' => "integer",
        'score' => "integer",
        'sort_order' => "integer",
        'priority' => "integer",
        'start_date' => "date",
        'end_date' => "date",
        'target_date' => "date",

        'user_id' => "integer",
        'category_id' => "integer",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
