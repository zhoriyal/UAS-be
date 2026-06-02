<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'file_name',
        'file_type',
        'file_size',
        'file_path',
        'status',
        'category',
        'verification_status',
        'payment_month',
        'payment_year',
    ];
}
