<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function user()
    {
        return $this->belongsTo(AppUsers::class);
    }
    public function bookings()
{
    return $this->hasMany(Booking::class);
}
public function area()
{
    return $this->belongsTo(Area::class);
}
public function coupon()
{
    return $this->belongsTo(Coupon::class);
}

}
