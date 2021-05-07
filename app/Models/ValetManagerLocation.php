<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValetManagerLocation extends Model
{
    use HasFactory;

    public function manager(){
        return $this->belongsTo(ValetManager::class);
    }

    public function valets(){
        return $this->hasMany(Valet::class);
    }

    public function poolTips(){
        return $this->hasMany(PoolTip::class,'id','location_id');
    }
}
