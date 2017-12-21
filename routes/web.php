<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Main Routes
 */

Route::get('/', function () {
	return 'This is base URL';
});

/**
 * Auth Routes
 */

Route::prefix('auth')->group(function () {

    // Captcha Routes
	Route::prefix('captcha')->group(function () {
	    Route::get('/', 'Auth\\CaptchaController@index');
	    Route::get('/key', 'Auth\\CaptchaController@key');
    });

	// Login Routes
	Route::any('login', 'Auth\\LoginController@index');

});

