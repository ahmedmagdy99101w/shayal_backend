<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionType extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function options()
    {
        return $this->hasMany(Options::class,'option_type_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
