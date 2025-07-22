<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiFeedback extends Model
{
    use HasFactory;

    protected $fillable = ['diary_id', 'summary', 'advice', 'raw_response'];

    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }
}
