<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function user()
    {
        return $this->belongsTo(AppUsers::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    protected $casts = [
        'apartment_id'=>'integer',
        'user_id'=>'integer',
        'rating'=>'double',

    ];
}
