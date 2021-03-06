<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValetManager extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function locations(){
        return $this->hasMany(ValetManagerLocation::class);
    }

    public function valets(){
        return $this->hasMany(Valet::class);
    }

    public function poolTips(){
        return $this->hasMany(PoolTip::class);
    }
}
