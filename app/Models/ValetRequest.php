<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValetRequest extends Model
{
    use HasFactory;

    public function vehicleRequest(){
        return $this->hasOne(VehicleRequest::class);
    }
}
