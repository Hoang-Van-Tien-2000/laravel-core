<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => getConfig('routes.web.as')], function () {
    Route::get('/', ['uses' => 'HomeController@index'])->name('home');
});
