<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSecurityVisitor extends Model
{
    use HasFactory;
    protected $table = 'school_security_visitor';

    protected $fillable = [
        'building_id',
        'id',
        'date',
        'visitor_id',
        'card_id',
        'is_card_return',
        'reader_id',
        'visitor_name',
        'class',
        'section',
        'out_time_remark',
        'student_name',
        'mobile',
        'whatsapp',
        'email',
        'id_proof',
        'photo',
        'added_by',
        'school_id',
        'in_time',
        'out_time',
        'status',
        'created_at',
        'visiter_purpose',
        'visitor_id_detected',
        'visitor_block',
        'updated_at',
    ];


    public function SchoolAdminSecurity()
    {
        return $this->belongsTo(SchoolAdminSecurity::class, 'added_by');
    }
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
}
