<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValetManagerLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valet_manager_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valet_manager_id');
            $table->string('name');
            $table->string('address');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->timestamps();

            $table->foreign('valet_manager_id')->references('id')->on('valet_managers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valet_manager_locations');
    }
}
