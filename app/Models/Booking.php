<?php

namespace App\Models;

use Carbon\Carbon;
use Google\Auth\Cache\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
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
    
    public function item_moveds()
    {
        return $this->belongsToMany(ItemMoved::class, 'booking_item_moveds', 'booking_id', 'item_moveds_id');
    }
    
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
