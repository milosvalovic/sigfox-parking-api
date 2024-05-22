<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/parking_lot/list', 'Api\Parking@parking_lots_list');
Route::post('/parking_lot/detail', 'Api\Parking@parking_lot_detail');
Route::post('/parking_lot/change_state', 'Api\Parking@change_state');
Route::post('/parking_lot/create_spots', 'Api\Parking@create_spots');
Route::post('/parking_lot/notify_me', 'Api\Parking@notify_when_freed_up');
Route::get('/notification/send', 'Api\Notifications@sendNotifications');
Route::get('/parking_lot/parking_spots/{parking_lot_id}', 'Api\Parking@get_parking_spots');
Route::get('/test', 'Api\Parking@test');

/* USER */
Route::post('/user/register', 'Api\User@register');
Route::post('/user/login', 'Api\User@login');

Route::group(['middleware' => ['apiuser']], function () {
    /* CAR*/
    Route::get('/car/list', 'Api\Car@getCarList');
    Route::post('/car/add', 'Api\Car@addCar');
    Route::delete('/car/delete/{carID}', 'Api\Car@deleteCar');

    /* RESERVATION */

    Route::get('/reservation/list', 'Api\Reservation@reservationList');
    Route::post('/reservation/create', 'Api\Reservation@createReservation');
    Route::get('/reservation/delete/{reserve_id}', 'Api\Reservation@deleteReservation');
    Route::get('/reservation/list', 'Api\Reservation@reservationList');

});

Route::get('/reservation/prepareFirstStage/{device_type_identifier}/{parking_lot_id}', 'Api\Parking@prepareSecondStage');

