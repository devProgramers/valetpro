<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoolTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pool_tips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valet_manager_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('valet_id');
            $table->integer('amount');
            $table->boolean('status')->default(0)->comment('0:unpaid, 1:paid');
            $table->timestamps();

            $table->foreign('valet_manager_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('valet_manager_locations');
            $table->foreign('request_id')->references('id')->on('valet_requests');
            $table->foreign('valet_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pool_tips');
    }
}
