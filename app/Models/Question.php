<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'question',
        'answer',
        'status',
    ];
}
