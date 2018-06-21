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

Route::get('logout', 'Auth\LoginController@logout');

Route::get('login/{provider}', 'Auth\SocialController@redirectToProvider');
Route::get('login/{provider}/callback', 'Auth\SocialController@handleProviderCallback');


Route::post('login/{provider}/test', 'Auth\SocialController@loginUser');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
