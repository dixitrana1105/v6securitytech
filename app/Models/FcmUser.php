<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FcmUser extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'user_id',
        'user_table_id',
        'user_type',
        'fcm_token',
    ];

    public $timestamps = true;
}