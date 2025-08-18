<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'type', 'meta', 'occurred_at'];

    protected $casts = [
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];
}
