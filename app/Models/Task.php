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
        'isComplete',
        'user_id',
    ];

    protected $casts = [
        'title' => "string",
        'notes' => "string",
        'isComplete' => "boolean",
        'user_id' => "integer",
    ];
}
