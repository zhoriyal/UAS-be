<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deadline extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'due_date',
        'status',
        'is_urgent',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_urgent' => 'boolean',
    ];
}
