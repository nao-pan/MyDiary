<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'new_users',
        'posts',
        'd1_retention',
        'd7_retention',
        'wau',
        'mau',
        'weekly_3plus_ratio',
    ];
}
