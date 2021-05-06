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
    Route::get('valet_status/{id}','ValetRequestController@valetStatus');
});
Route::GROUP(['namespace'=>'App\Http\Controllers\Api','middleware' => ['auth:sanctum']], function () {
    Route::post('signup/valet','userController@signUpValet');
    Route::get('user/edit/{id}','userController@edit');
    Route::get('get_locations','userController@getLocations');
    Route::post('user/update/{id}','userController@update');
    Route::post('show/locations','ValetRequestController@locations');
    Route::post('request/valet','ValetRequestController@requestValet');
    Route::get('valets/list','ValetRequestController@getValetsList');
    Route::post('assign/valet/{id}','ValetRequestController@assignValet');
    Route::get('view/assigned_requests','ValetRequestController@assingedRequests');
    Route::get('respond/request/{status}/{id}','ValetRequestController@respondRequest');
    Route::get('complete/request/{id}','ValetRequestController@completeRequest');
    Route::get('ticket','VehicleRequestController@getTicket');
    Route::post('request/vehicle','VehicleRequestController@requestVehicle');
    Route::get('respond/vehicle/request/{status}/{id}','VehicleRequestController@respondRequest');
});
