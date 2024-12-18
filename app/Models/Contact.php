<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = ['snapchat', 'tiktok', 'twitter', 'google', 'whatsapp', 'instagram', 'phone','phone2'];
}
