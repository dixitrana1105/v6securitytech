<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class BlockVisitor extends Authenticatable
{
    use HasFactory;

    protected $table = 'block_visitor';

    protected $fillable = [
        'id',
        'building_id',
        'visitor_id',
        'tenant_id',
        'table_id',
        'block_from',
        'added_by',
        'block_tenant_remark',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;
}
