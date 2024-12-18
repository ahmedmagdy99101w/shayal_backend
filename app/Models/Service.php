<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'photo','status','duration'];


  
    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
    public function optionTypes()
    {
        return $this->hasMany(OptionType::class);
    }
    protected $casts = [
        'price' => 'integer',
        'status' => 'string',
    ];
    
}

