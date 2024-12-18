<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function user()
    {
        return $this->belongsTo(AppUsers::class);
    }
    public function booked()
    {
        return $this->belongsTo(Booked_apartment::class);
    }
    protected $casts = [
        'point'=>'integer',
    ];
}
