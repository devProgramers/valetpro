<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valet extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function manager(){
        return $this->hasOne(ValetManager::class);
    }
    public function tips(){
        return $this->hasMany(DirectTip::class);
    }
}
