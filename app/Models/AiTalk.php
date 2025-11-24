<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AiTalk extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'message',
        'user_flag',
        'ai_main_id',
        'user_id',
        'current_talk_id',
        // 'talk_id',
    ];

    protected $casts = [
        'message' => 'string',
        'user_flag' => 'string',
        'ai_main_id' => 'string',
        'user_id' => 'string',
        'current_talk_id' => 'string',
    ];

    // protected $fillable = []
}
