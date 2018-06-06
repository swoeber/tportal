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

Route::get('/', function () {
    return view('welcome');
});

Route::resource('/albums', "AlbumsController", ['only' => ['index']]);

Route::get('login/facebook', 'Auth\LoginController@redirectToProvider');
Route::get('login/facebook/callback', 'Auth\LoginController@handleProviderCallback');

Route::get('login/google', 'Auth\LoginController@redirectToProvider');
<<<<<<< HEAD
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');
=======
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');
>>>>>>> ccf0d2afd1f2f6073c7fc0c212559db5f646e91f
