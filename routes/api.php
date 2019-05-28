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

    $api->group([ 'prefix' => 'ipn', 'as' => 'ipn.'], function ($api){
        $api->post('validation', 'App\Http\Controllers\API\PaymentController@validatePayload')->name('validation');
        $api->post('confirmation', 'App\Http\Controllers\API\PaymentController@confirmationPayload')->name('confirmation');
        $api->post('stk/{ref}', 'App\Http\Controllers\API\PaymentController@stkCallback')->name('stk_callback');
        $api->post('stk-timeout', 'App\Http\Controllers\API\PaymentController@queueTimeout')->name('stk_timeout');
        $api->post('b2c', 'App\Http\Controllers\API\PaymentController@b2cCallback')->name('b2c_callback');
        $api->post('b2c-timeout', 'App\Http\Controllers\API\PaymentController@b2cTimeout')->name('b2c_timeout');
    });

    $api->group(['prefix' => 'auth'], function ($api) {
        
        $api->post('register', 'App\Http\Controllers\API\AuthController@register');

        $api->post('login', 'App\Http\Controllers\API\AuthController@login');

        $api->post('google', 'App\Http\Controllers\API\AuthController@authGoogle');
    });

    $api->group(['middleware' => 'api.auth'], function ($api) {

        $api->group(['prefix' => 'auth'], function ($api) {
            $api->post('logout', 'App\Http\Controllers\API\AuthController@logout');

            $api->post('getPhoneCode', 'App\Http\Controllers\API\AuthController@getOTP');
    
            $api->post('activate/Phone', 'App\Http\Controllers\API\AuthController@activatePhone');
    
            $api->post('activate/email', 'App\Http\Controllers\API\AuthController@activateEmail');
        });

        $api->group(['prefix' => 'user'], function ($api) {
            $api->get('dashboard', 'App\Http\Controllers\API\DashboardController@index');
            $api->get('club/options', 'App\Http\Controllers\API\ClubController@user_options');
            $api->post('membership', 'App\Http\Controllers\API\UserController@registerClub');
            $api->get('clubs', 'App\Http\Controllers\API\ClubController@memberships');
        });

        $api->get('club/options', 'App\Http\Controllers\API\ClubController@options');

        $api->get('clubs', 'App\Http\Controllers\API\ClubController@index');

        $api->get('booking/{id}', 'App\Http\Controllers\API\GameController@getBooked')->where('id', '[0-9]+');

        $api->post('booking', 'App\Http\Controllers\API\GameController@create');

        $api->get('games/history', 'App\Http\Controllers\API\GameController@index');

        $api->get('games', 'App\Http\Controllers\API\GameController@index');

        $api->get('available', 'App\Http\Controllers\API\GameController@available');

        $api->get('payment/{account}', 'App\Http\Controllers\API\GameController@paymentDetails');

        $api->post('pay', 'App\Http\Controllers\API\PaymentController@pay');

        $api->group(['middleware' => 'bindings'], function ($api){
            $api->group(['middleware' => 'game.creator'], function ($api){
                $api->group(['prefix' => 'game/{game}'], function($api){
                    $api->post('send-invite', 'App\Http\Controllers\API\GameController@sendInvite');
                    $api->post('edit', 'App\Http\Controllers\API\GameController@update');
                });
            });
        });

    });

});
