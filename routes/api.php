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
Route::GROUP(['namespace'=>'App\Http\Controllers\Api'], function () {
    Route::get('get_roles','userController@getRoles');
    Route::post('signup','userController@signUp');
    Route::post('signin','userController@signIn');
    Route::post('forgot/password','userController@forgotPassword');
    Route::post('update/password','userController@updatePassword');
});
Route::GROUP(['namespace'=>'App\Http\Controllers\Api','middleware' => ['auth:sanctum']], function () {
    //users
    Route::get('user/edit/{id}','userController@edit');
    Route::post('user/update/{id}','userController@update');
    //customers
    Route::post('show/locations','ValetRequestController@locations');
    Route::post('request/valet','ValetRequestController@requestValet');
    Route::get('ticket','VehicleRequestController@getTicket');
    Route::post('request/vehicle','VehicleRequestController@requestVehicle');
    Route::post('tip/valet','TipsController@tip');
    //valet managers
    Route::post('signup/valet','userController@signUpValet');
    Route::get('get_locations','userController@getLocations');
    Route::get('valets/list','ValetRequestController@getValetsList');
    Route::post('assign/valet/{id}','ValetRequestController@assignValet');
    Route::get('requests/list','ValetRequestController@requestList');
    Route::get('request/{id}','ValetRequestController@singleRequest');
    Route::post('set/tip/type','TipsController@setTipType');
    //valets
    Route::get('view/assigned_requests','ValetRequestController@assingedRequests');
    Route::get('respond/request/{status}/{id}','ValetRequestController@respondRequest');
    Route::get('complete/request/{id}','ValetRequestController@completeRequest');
    Route::get('respond/vehicle/request/{status}/{id}','VehicleRequestController@respondRequest');

    //broadcasts
    Route::get('valet_status/{id}','ValetRequestController@valetStatus');
    Route::get('vehicle_status/{id}','VehicleRequestController@vehicleStatus');
});
