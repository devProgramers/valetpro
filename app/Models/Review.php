<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    public function valet(){
        return $this->belongsTo(User::class,'valet_id','id');
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id','id');
    }
}
