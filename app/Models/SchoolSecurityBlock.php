<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSecurityBlock extends Model
{
    use HasFactory;
    protected $table = 'school_security_visitor_block';

    protected $fillable = [
        'id',
        'date',
        'school_id',
        'visitor_id',
        'visitor_name',
        'class',
        'visitor_id_detected',
        'section',
        'student_name',
        'mobile',
        'whatsapp',
        'email',
        'id_proof',
        'photo',
        'blocked_by',
        'created_at',
        'updated_at',
        'remark'
    ];

}
