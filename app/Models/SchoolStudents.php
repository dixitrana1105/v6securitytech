<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolStudents extends Model
{
    use HasFactory;
    protected $table = 'school_student';

    protected $fillable = [
        'id',
        'student_id',
        'name',
        'middle',
        'last',
        'class',
        'section',
        'mobile',
        'whatsapp',
        'email',
        'guardian',
        'status',
        'reader_id',
        'school_id',
        'created_at',
        'updated_at',
    ];

    public function reader()
    {
        return $this->belongsTo(Reader::class, 'reader_id');
    }
}
