<?php

namespace App\Http\Controllers\api;

use App\Models\Notification;
use App\Models\Parking_lot;

use App\Models\Parking_spot;
use App\Models\Partner;
use App\Models\Reserve;
use App\Models\Testing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mockery\Matcher\Not;

class Parking extends Controller
{
    public function last_status(){


        return \GuzzleHttp\json_encode(null);
    }

    public function parking_lots_list(Request $request){
        $offset = $request->input('offset',0);
        $limit = $request->input('limit',50);
        $lat = $request->input('lat',0.0);
        $lng = $request->input('lng',0.0);
        $charger = $request->input('only_charger',0.0);

        $data = Parking_lot::with("partner")
            ->selectRaw("*, ( 6371000 * acos( cos( radians(?) ) *
                       cos( radians( parking_lot_lat ) )
                       * cos( radians( parking_lot_lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( parking_lot_lat ) ) )
                     )/1000 AS distance,
                      (SELECT COUNT(parking_spot_id) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id ) AS parking_lot_total_spots,
                      (SELECT COUNT(parking_spot_id) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id AND parking_spot_occupied = 0) AS parking_lot_available_spots,
                      (SELECT IF(COUNT(parking_spot_id) > 0, TRUE, FALSE) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id AND parking_spot_has_charger = 1) AS parking_lot_has_charger", [$lat, $lng, $lat])
            ->offset($offset)
            ->limit($limit)
            ->orderby("distance", "ASC")
            ->get();
        //$result["partner"] = Partner::selectRaw("*")->where("partner_id", $result["parking_lot_id_partner"])->get();


        return response()->json([
            'result' => true,
            'data' => $data
        ]);

    }

    public function parking_lot_detail(Request $request){
        $lat = $request->input('lat',0.0);
        $lng = $request->input('lng',0.0);
        $id = $request->input('parking_lot_id',0);

        $data = Parking_lot::with("partner")
            ->selectRaw("*, ( 6371000 * acos( cos( radians(?) ) *
                       cos( radians( parking_lot_lat ) )
                       * cos( radians( parking_lot_lng ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( parking_lot_lat ) ) )
                     )/1000 AS distance,
                      (SELECT COUNT(parking_spot_id) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id ) AS parking_lot_total_spots,
                      (SELECT COUNT(parking_spot_id) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id AND parking_spot_occupied = 0) AS parking_lot_available_spots,
                      (SELECT IF(COUNT(parking_spot_id) > 0, TRUE, FALSE) FROM parking_spot WHERE parking_spot_id_parking_lot = parking_lot_id AND parking_spot_has_charger = 1) AS parking_lot_has_charger", [$lat, $lng, $lat])
            ->orderby("distance", "ASC")
            ->where('parking_lot_id', '=', $id)
            ->first();
        //$result["partner"] = Partner::selectRaw("*")->where("partner_id", $result["parking_lot_id_partner"])->get();


        return response()->json([
            'result' => true,
            'data' => $data
        ]);


    }


    public function change_state(Request $request){
        $states = $request->input('states',0);
        $parking_lot_id = $request->input('parking_lot_id',0);
        $code = $request->input('code',0);
        $device_type_identifier = $request->input('device_type_identifier',0);

        switch ($code){
            case 0:
                $this->setStates($states, $parking_lot_id);
                break;
            case 1:
                $this->prepareFirstStage($device_type_identifier);
                break;
            case 2:
                $this->prepareSecondStage($device_type_identifier, $parking_lot_id);
                break;
            case 3:
                $this->prepareThirdStage($device_type_identifier);
                break;
            default :
                $this->setStates($states, $parking_lot_id);
                break;
        }


        return response()->json([
            'result' => true,
        ],200);



    }


    public function create_spots(Request $request){
        $spots = $request->input('number_of_spots',0);
        if($spots == 0) {
            return;
        }

        $parking_lot_id = $request->input('parking_lot_id',0);
        if($parking_lot_id == 0){
            return;
        }
        $count = Parking_spot::where('parking_spot_id_parking_lot', $parking_lot_id)->count();
        /*var_dump($count);
        die;*/
        for($i = 0; $i<$spots; $i++) {
            ++$count;
            $spot = new Parking_spot();
            $spot->parking_spot_id_parking_lot = $parking_lot_id;
            $spot->parking_spot_occupied = 0;
            $spot->parking_spot_number = $count;
            $spot->save();
        }
    }

    public function get_parking_spots($parking_lot_id){
        $date = date('Y-m-d');
        //$result = Parking_spot::selectRaw('*, (SELECT COUNT(reserve_id) FROM reserve WHERE reserve_id_spot = parking_spot_id AND reserve_for_date = DATE(\'' . $date . '\') ) as parking_spot_reserved')->where("parking_spot_id_parking_lot", $parking_lot_id)->get();
        $result = Parking_spot::with(['reservation' => function($q) use($date){
            $q->where('reserve.reserve_for_date', '>', $date);
            return $q;
        }])->selectRaw('*, (SELECT COUNT(reserve_id) FROM reserve WHERE reserve_id_spot = parking_spot_id AND reserve_for_date = DATE(\'' . $date . '\') ) as parking_spot_reserved')->where("parking_spot_id_parking_lot", $parking_lot_id)->get();
        return response()->json([
            'result' => true,
            'data' => $result
        ]);
    }

    public function notify_when_freed_up(Request $request){
        $parking_lot_id = $request->input('parking_lot_id',0);
        if($parking_lot_id == 0){
            return response()->json([
                'result' => false,
                'error' => 'Chýba ID parkoviska'
            ],400);
        }
        $firebase_token = $request->input('firebase_token',null);
        if($firebase_token == null){
            return response()->json([
                'result' => false,
                'error' => 'Chýba firebase token'
            ], 400);
        }

        $result = Notification::select('*')->where([['notification_firebase_token', '=',$firebase_token], ['notification_id_parking_lot', '=',$parking_lot_id]])->get();

        if(count($result) > 0){
            return response()->json([
                'result' => false,
                'error' => 'Požiadavka nemôže byť splnená, nakoľko ste už žiadali o upozornenie'
            ],400);
        }

        $notifaction = new Notification();
        $notifaction->notification_id_parking_lot = $parking_lot_id;
        $notifaction->notification_firebase_token = $firebase_token;

        return response()->json([
            'result' => $notifaction->save()
        ]);

    }

    public function test(){

        return view('email_verified');
    }



    // First Stage prepares the time synchronization
    // Should happen right after reservation synchronisation

    public function prepareThirdStage($device_type_identifier){
        $data = \GuzzleHttp\json_encode([
            "downlinkDataString"=> "0000000000000000",
            "downlinkMode"=> 2,
            ]);

        $url = "api.sigfox.com/v2/device-types/". $device_type_identifier ;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERPWD, '6028fcb941758107b0ce2d0d' . ":" . '987c6cc0f201dfa9d0558414142dcd82');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POSTREDIR, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);

    }

    public function prepareFirstStage($device_type_identifier){
        $data = \GuzzleHttp\json_encode([
            "downlinkDataString"=> "{time}00000000",
            "downlinkMode"=> 0]);

        $url = "api.sigfox.com/v2/device-types/". $device_type_identifier ;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERPWD, '6028fcb941758107b0ce2d0d' . ":" . '987c6cc0f201dfa9d0558414142dcd82');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POSTREDIR, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);

    }

    public function prepareSecondStage($device_type_identifier, $parking_lot_id){
        $date = date('Y-m-d');
        $reservations = Reserve::with(['parking_lot' => function($q) use($parking_lot_id){
            $q->where('parking_lot.parking_lot_id', '=', $parking_lot_id);
        }])->with(['spot' => function($q){
            $q->orderBy('parking_spot_number', 'asc');
        }])->select('*')->where([['reserve_for_date', '=', $date]])->get();
        $bin_str = "";

        for($i = 1; $i<=32; $i++){
            foreach ($reservations as $item){
                if($item->spot->parking_spot_number == $i){
                    $bin_str .= "1";
                } else {
                    $bin_str .= "0";
                }
            }
        }


        $hex = dechex(bindec($bin_str));



        $data = \GuzzleHttp\json_encode([
            "downlinkDataString"=> str_pad($hex, 9-strlen($hex), "0", STR_PAD_LEFT) . '00000000',
            "downlinkMode"=> 0
            ]);

        $url = "api.sigfox.com/v2/device-types/". $device_type_identifier ;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERPWD, '6028fcb941758107b0ce2d0d' . ":" . '987c6cc0f201dfa9d0558414142dcd82');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POSTREDIR, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);

    }

    public function setStates($states, $parking_lot_id){
        $bin = decbin($states);
        $bin_arr = str_split($bin);
        $result = Parking_spot::select("*")->where("parking_spot_id_parking_lot", $parking_lot_id)->get();

        for($i=0;$i<sizeof($result);$i++){
            $spot = $result[$i];
            if($i < sizeof($bin_arr)){
                $spot->parking_spot_occupied = $bin_arr[sizeof($bin_arr)-$i-1];
            } else {
                $spot->parking_spot_occupied = 0;

            }
            $spot->save();

            $testing = new Testing();
            $testing->testing_id_parking_spot = $spot->parking_spot_id;
            $testing->testing_spot_occupied = $spot->parking_spot_occupied;
            $testing->testing_created_unixtime = time();
            $testing->save();
        }
    }


}
