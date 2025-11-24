<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'version',
        'is_active',
        'token',
        'user_id',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'version' => 'string',
        'is_active' => 'bool',
        'token' => 'string',

        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
