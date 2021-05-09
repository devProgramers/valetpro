<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_tips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valet_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('request_id');
            $table->integer('amount');
            $table->boolean('status')->default(0)->comment('0:unpaid, 1:paid');
            $table->timestamps();


            $table->foreign('valet_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('request_id')->references('id')->on('valet_requests');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('direct_tips');
    }
}
