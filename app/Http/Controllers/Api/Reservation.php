<?php

namespace App\Http\Controllers\api;

use App\Models\Reserve;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;


class Reservation extends Controller
{
    public function reservationList(Request $request)
    {
        $user = $request->user;
        $reservations = Reserve::with('parking_lot')->with('car')->with('spot')->where('reserve_id_user', '=', $user->user_id)->orderBy('reserve_for_date', 'desc')->get();
        $plates = array();
        $decoded_res = array();

        foreach ($reservations as $item){
            $plate = "";
            $plate = Crypt::decryptString($item->car->car_licence_plate);


            //$item->car->car_licence_plate = $plate;
            //$item->car->car_licence_plate = $plate;

            array_push($plates, $plate);
        }

        for ($i = 0; $i < count($reservations); $i++) {
            $reservations[$i]->car->car_licence_plate = $plates[$i];
        }



        return response()->json([
            'result' => true,
            'data' => $reservations
        ]);
    }

    public function createReservation(Request $request)
    {
        $user = $request->user;

        $car_id = $request->input('car_id');
        $date = $request->input('reserve_for_date');
        if (!isset($date)) {
            $date = date('Y-m-d', strtotime("+1 day"));
        }
        if ($date <= date('Y-m-d')) {
            return response()->json([
                'result' => false,
                'error' => "Rezervácia musí byť aspoň na nasledujúci deň."
            ], 400);
        }


        if (!isset($car_id)) {
            return response()->json([
                'result' => false,
                'error' => "Musíte zvoliť auto"
            ], 400);
        }
        $spot_id = $request->input("spot_id");
        if (!isset($spot_id)) {
            return response()->json([
                'result' => false,
                'error' => "Musíte vybrať prakovacie miesto"
            ], 400);
        }


        $reserve = Reserve::select('*')->where([['reserve_id_spot', '=', $spot_id], ['reserve_for_date', '=', $date]])->count();
        if ($reserve > 0) {
            return response()->json([
                'result' => false,
                'error' => "Toto miesto je už rezervované"
            ], 400);
        }

        $reservation = new Reserve();
        $reservation->reserve_id_car = $car_id;
        $reservation->reserve_id_spot = $spot_id;
        $reservation->reserve_id_user = $user->user_id;
        $reservation->reserve_for_date = $date;
        $reservation->reserve_sent = 0;
        $reservation->save();


        return response()->json([
            'result' => true,
            'data' => $reservation
        ]);
    }

    public function deleteReservation(Request $request, $reserve_id)
    {
        $user = $request->user;

        $success = Reserve::where([['reserve_id_user', '=', $user->user_id], ['reserve_id', '=', $reserve_id]])->delete() == 1 ? TRUE : FALSE;
        return response()->json([
            'result' => $success
        ]);
    }

    // First Stage prepares the time synchronization
    // Should happen right after reservation synchronisation
    public function prepareSecondStage($device_type_identifier)
    {
        $data = \GuzzleHttp\json_encode(["downlinkDataString" => "{time}00000000"]);

        $url = "https://api.sigfox.com/v2/device-types/" . $device_type_identifier;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERPWD, '6028fcb941758107b0ce2d0d' . ":" . '987c6cc0f201dfa9d0558414142dcd82');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return response()->json([
            'result' => $httpCode == 204 ? TRUE : FALSE
        ], $httpCode);
    }

    public function prepareFirstStage($device_type_identifier, $parking_lot_id)
    {
        $date = date('Y-m-d');
        $reservations = Reserve::with(['parking_lot' => function ($q) use ($parking_lot_id) {
            $q->where('parking_lot.parking_lot_id', '=', $parking_lot_id);
        }])->with(['spot' => function ($q) {
            $q->orderBy('parking_spot_number', 'asc');
        }])->select('*')->where([['reserve_for_date', '=', $date]])->get();
        $bin_str = "";

        for ($i = 1; $i <= 32; $i++) {
            foreach ($reservations as $item) {
                if ($item->spot->parking_spot_number == $i) {
                    $bin_str .= "1";
                } else {
                    $bin_str .= "0";
                }
            }
        }

        $hex = dechex(bindec($bin_str));

        $data = \GuzzleHttp\json_encode(["downlinkDataString" => $hex]);

        $url = "https://api.sigfox.com/v2/device-types/" . $device_type_identifier;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERPWD, '6028fcb941758107b0ce2d0d' . ":" . '987c6cc0f201dfa9d0558414142dcd82');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return response()->json([
            'result' => true,
            'data' => $hex
        ]);
    }

}
