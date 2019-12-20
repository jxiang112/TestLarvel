<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('getUserById', 'UserController@getUserById');
Route::get('getUsers', 'UserController@getUsers');
Route::get('insertuser', 'UserController@insertUser');
Route::match(['GET', 'POST'], 'test', 'UserController@test')->name('test')->middleware('token');

Route::any('CommonResponse', function(Request $request) {
    return 'CommonResponse';
})->name('CommonResponse');

Route::post('login', 'UserController@login');
/*Route::get('usertest', function(Request $request){
    return "name ok";
});*/
