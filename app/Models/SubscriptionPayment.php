<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    public function membership()
    {
        return $this->belongsTo(Membership::class,'membership_id');
    }
}
