<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTalkHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'emotion_data',
        'select_speaker',
        'user_id',
        'ai_agent_id',
        'ai_talk_session_id',
        // 'task_id',
        // 'category_id',
    ];

    protected $casts = [
        'message' => 'string',
        'emotion_data' => 'string',
        'select_speaker' => 'integer',
        'user_id' => 'integer',
        'ai_agent_id' => 'integer',
        'ai_talk_session_id' => 'integer',
        'task_id' => 'integer',
        'category_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ai_agent()
    {
        return $this->belongsTo(AiAgent::class);
    }
    public function ai_talk_session()
    {
        return $this->belongsTo(AiAgent::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
