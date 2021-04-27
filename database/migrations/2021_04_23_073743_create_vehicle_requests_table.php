<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valet_request_id');
            $table->string('longitude');
            $table->string('latitude');
            $table->time('ready_at');
            $table->integer('status')->default(0)->comment('0:requested, 1:accepted, 2:arrived, 3:completed,4:canceled');
            $table->timestamps();

            $table->foreign('valet_request_id')->references('id')->on('valet_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_requests');
    }
}
