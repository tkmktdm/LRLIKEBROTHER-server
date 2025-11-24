<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'sort_order',
        'color',
        'user_id',
    ];

    protected $casts = [
        'name' => 'string',
        'sort_order' => 'integer',
        'color' => 'string',

        'user_id' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
