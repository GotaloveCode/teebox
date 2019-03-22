<?php

namespace App\Providers;

use Dingo\Api\Provider\DingoServiceProvider;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

//        Validator::extend('phone', function($attribute, $value, $parameters, $validator) {
//            return preg_match("/^0[1-9][0-9][ ]?[0-9]{7}$/", $value);
//        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
