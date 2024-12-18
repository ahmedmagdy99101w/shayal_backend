<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function user()
{
    return $this->belongsTo(AppUsers::class);
}
public function subscription()
{
    return $this->belongsTo(Subscription::class);
}
}
