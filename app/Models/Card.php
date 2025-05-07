<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        // 'reader_id',
        'serial_id',
        'assign_status',
    ];

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building_Master::class);
    }

    public function reader()
    {
        return $this->belongsTo(Reader::class);
    }

    public function visitor()
    {
        return $this->hasOne(Visitor::class); // assuming one card can be assigned to one visitor
    }
    public function visitor_master()
{
    return $this->hasOne(Visitor_Master::class, 'card_id', 'id');
}


}

?>
