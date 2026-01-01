<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTalkSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_agent_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'ai_agent_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ai_agent()
    {
        return $this->belongsTo(AiAgent::class);
    }
}
