<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSpotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_spot', function (Blueprint $table) {
            $table->bigIncrements('parking_spot_id');
            $table->smallInteger('parking_spot_occupied')->default(0);
            $table->smallInteger('parking_spot_has_charger')->default(0);
            $table->bigInteger('parking_spot_id_parking_lot');
            $table->bigInteger('parking_spot_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_spot');
    }
}
