<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Subscription extends Model
{
    protected $table = 'subscriptions';
    protected $guarded =[];

    public function isExpired()
    {

        return Carbon::now()->greaterThan($this->expires_at);
    }

    public function isVisitLimitReached()
    {
        return $this->visits >= $this->service->visit_limit;
    }
   
    public function users()
    {
        return $this->belongsToMany(AppUsers::class,'memberships','subscription_id')->withPivot('expire_date','visit_count');
    }
   protected $casts = [
    'visits' => 'integer',
    'price' => 'integer',
    'offer' => 'integer',
    'price_offer' => 'integer',
     'duration' => 'integer'
    
];

}
