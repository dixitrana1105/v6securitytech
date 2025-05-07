<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadersLog extends Model
{
    use HasFactory;

    protected $table = 'readers_logs'; // Table name

    protected $fillable = [
        'message',
        'building_id',
        'card_id',
        'visitor_id',
        'is_read',
        'comments',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public $timestamps = false; // Since you don't have created_at and updated_at

    // Relationships (if needed)
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
}
