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
    Route::post('signup/valet','userController@signUpValet');
    Route::get('user/edit/{id}','userController@edit');
    Route::post('user/update/{id}','userController@update');
});
