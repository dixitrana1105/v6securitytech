<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Visitor_Master extends Authenticatable
{
    use HasFactory;

    protected $table = 'visitor_master';

    protected $fillable = [
        'id',
        'card_returned',
        'visitor_id',
        'card_id',
        'is_card_return',
        'reader_id',
        'visiter_purpose',
        'date',
        'photo',
        'tenant_block',
        'visitor_id_detected',
        'building_id',
        'mobile', 'whatsapp',
        'status',
        'full_name',
        'out_time',
        'id_proof',
        'out_time_remark',
        'in_time',
        'pre_approve_tenant_visitore_id',
        'tenant_flat_office_no',
        'added_by',
        'created_at',
        'updated_at',
    ];

    public function Building_Master()
    {
        return $this->belongsTo(Building_Master::class, 'building_id');
    }

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function BlockVisitor()
    {
        return $this->hasOne(BlockVisitor::class, 'table_id', 'id');
    }
}
