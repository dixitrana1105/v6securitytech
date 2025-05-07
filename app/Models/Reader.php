<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reader extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'building_id',
        'serial_id'
    ];

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building_Master::class);
    }

    public function visitor()
    {
        return $this->hasOne(Visitor::class); // assuming one card can be assigned to one visitor
    }
}

?>
