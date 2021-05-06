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
    public function location(){
        return $this->hasOne(ValetManagerLocation::class,'id','location_id');
    }
    public function customers(){
        return $this->hasOne(User::class,'id','customer_id');
    }
}
