<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => getConfig('routes.admin.as')], function () {
    Route::group(['middleware' => 'auth:admin'], function () {
        // dashboard
        Route::get('/', ['uses' => 'HomeController@index'])->name('home');
    });
});
