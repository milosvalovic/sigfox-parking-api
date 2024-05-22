<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class Car extends Controller
{
    public function getCarList(Request $request){
        $user = $request->user;

        $cars = \App\Models\Car::select('*')->where('car_id_user', $user->user_id)->get();

        $decoded_cars = array();
        foreach ($cars as $item){
            $item->car_licence_plate = Crypt::decryptString($item->car_licence_plate);
            array_push($decoded_cars, $item);
        }


        return response()->json([
            'result' => true,
            'data' => $cars
        ]);
    }


    public function addCar(Request $request){
        $user = $request->user;

        $validation = $this->validateCar($request);
        if ($validation->fails()) {
            return response()->json([
                'result' => false,
                'error' => "Značka, model a ŠPZ vozidla musia byť vyplnené"
            ], 400);
        }

        $car = new \App\Models\Car();
        $car->car_brand = $request->input('car_brand');
        $car->car_model = $request->input('car_model');
        $car->car_licence_plate = Crypt::encryptString($request->input('car_licence_plate'));
        $car->car_color = $request->input('car_color');
        $car->car_year = $request->input('car_year');
        $car->car_id_user = $user->user_id;
        $car->save();
        return response()->json([
           'result' => true,
           'data' => $car
        ]);
    }

    public function deleteCar(Request $request, $carID){
        $success = \App\Models\Car::where([['car_id', '=',$carID], ['car_id_user', '=', $request->user->user_id]])->delete() == 1 ? TRUE : FALSE;
        return response()->json([
            'result' => $success
        ]);
    }

    public function validateCar($request)
    {
        $validator = Validator::make($request->all(), [
            'car_brand' => 'required',
            'car_model' => 'required',
            'car_licence_plate' => 'required',
        ]);

        return $validator;
    }

}
