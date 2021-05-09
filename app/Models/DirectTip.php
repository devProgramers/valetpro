<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectTip extends Model
{
    use HasFactory;

    public function valets(){
        return $this->hasMany(User::class,'id','valet_id');
    }

    public function valetManagers(){
        return $this->hasMany( ValetManager::class);
    }

    public function loctions(){
        return $this->hasMany( ValetManagerLocation::class);
    }

    public function customer(){
        return $this->belongsTo(User::class,'customer_id','id');
    }
}
