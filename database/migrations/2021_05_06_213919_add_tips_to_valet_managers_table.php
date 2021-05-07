<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipsToValetManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('valet_managers', function (Blueprint $table) {
            $table->boolean('tips')->after('company_name')->default(0)->comment('0:individual, 1:pooled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('valet_managers', function (Blueprint $table) {
            $table->dropColumn('tips');
        });
    }
}
