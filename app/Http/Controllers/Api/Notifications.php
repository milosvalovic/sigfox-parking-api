<?php


namespace App\Http\Controllers\Api;


use App\Models\Notification;
use Illuminate\Routing\Controller;
use App\Models\Parking_lot;
use Illuminate\Support\Facades\DB;

class Notifications extends Controller
{
    public function sendNotifications(){
        DB::enableQueryLog();
        $result = Notification::with("parking_lot")->with(["parking_spots" => function($query) {
            $query->where("parking_spot.parking_spot_occupied", "=", '0');
            return $query;
        }])->get();
        /*print_r(DB::getQueryLog());
        die;*/

        foreach($result as $item) {
            if (count($item->parking_spots) == 0) break;
            $headers = [
                'Authorization: key=AAAAfmIVwXg:APA91bE72kMtnlym-qVy1TLZGafABlU8xMypFjRl0EsBrK7hazOc9sTy3L_NU901xD0s7d-ZN5j63SSRxcdAZ1RAB0Oqg_JlYoJ5e1hiqBdad9EKPcON_9-IcO2xdWsmmvbgvnV4R-Js',
                'Content-Type: application/json'
            ];

            $body = 'Na parkovisku '. $item->parking_lot->parking_lot_name . ' sa uvoľnilo miesto. Parkovisko najdete na adrese: '. $item->parking_lot->parking_lot_city . ', ' . $item->parking_lot->parking_lot_street . ' '. $item->parking_lot->parking_lot_street_number;

            $payload = array(
                'to' => $item->notification_firebase_token,
                'priority' => 'high',
                'notification' => array(
                    'body' => $body,
                    'title' => "Uvoľnilo sa parkovacie miesto"
                )
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, \GuzzleHttp\json_encode($payload));
            curl_exec($ch);
            curl_close($ch);
            $item->delete();

        }
    }
}
