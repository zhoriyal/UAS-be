<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'status',
        'summary',
    ];
}
