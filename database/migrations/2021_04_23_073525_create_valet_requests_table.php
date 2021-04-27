<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValetRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valet_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('valet_id')->nullable()->default(null);
            $table->string('longitude');
            $table->string('latitude');
            $table->string('number_plate')->nullable();
            $table->integer('status')->default(0)->comment('0:requested, 1:assigned, 2:accepted, 3:completed, 4:canceled, 5:delivered');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('valet_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('valet_manager_locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valet_requests');
    }
}
