<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRequest extends Model
{
    use HasFactory;
    public function valetRequest(){
        return $this->belongsTo(ValetRequest::class);
    }
}
