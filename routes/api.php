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
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => 'cors'], function ($api) {

    $api->get('club/options', 'App\Http\Controllers\API\ClubController@options');

    $api->post('register', 'App\Http\Controllers\API\AuthController@register');

    $api->post('login', 'App\Http\Controllers\API\AuthController@login');

    $api->post('auth/google', 'App\Http\Controllers\API\AuthController@authGoogle');

    $api->group(['middleware' => 'api.auth'], function ($api) {

        $api->post('auth/logout', 'App\Http\Controllers\API\AuthController@logout');

        $api->post('auth/getPhoneCode', 'App\Http\Controllers\API\AuthController@getOTP');

        $api->post('auth/activate/Phone', 'App\Http\Controllers\API\AuthController@activatePhone');

        $api->post('auth/activate/email', 'App\Http\Controllers\API\AuthController@activateEmail');

        $api->get('booking/{id}', 'App\Http\Controllers\API\BookingController@getBooked')->where('id', '[0-9]+');

        $api->post('booking', 'App\Http\Controllers\API\BookingController@create');
    });
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
