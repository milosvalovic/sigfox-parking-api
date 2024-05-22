<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingLotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking_lot', function (Blueprint $table) {
            $table->bigIncrements('parking_lot_id');
            $table->text('parking_lot_name')->default(null);
            $table->double('parking_lot_lat',14,10);
            $table->double('parking_lot_lng',14,10);
            $table->text('parking_lot_city')->default(null);
            $table->text('parking_lot_street')->default(null);
            $table->text('parking_lot_street_number')->default(null);
            $table->text('parking_lot_description')->default(null);
            $table->bigInteger('parking_lot_id_partner');
            $table->bigInteger('parking_lot_capacity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_lot');
    }
}
