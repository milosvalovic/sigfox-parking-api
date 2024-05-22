<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkingLotSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('parking_lot')->insert([
            'parking_lot_name' => 'Miloš Parking 1',
            'parking_lot_lat' => '48.1344393',
            'parking_lot_lng' => '18.2498422',
            'parking_lot_city' => 'Mojzesovo',
            'parking_lot_street' => 'Mojzesovo',
            'parking_lot_street_number' => '14',
            'parking_lot_description' => 'Parkovicko u mňa doma. Otvorené non-stop.',
            'parking_lot_id_partner' => '1',
            'parking_lot_capacity' => '30',
        ]);
    }
}
