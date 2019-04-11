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

Route::get('/', 'HomeController@index')->name('home');
Route::get('/login', 'HomeController@index')->name('login');
Route::get('/login_with_twitch', 'HomeController@login_with_twitch')->name('login_with_twitch');
Route::get('/twitch_recirect', 'HomeController@twitch_recirect')->name('twitch_recirect');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/logout', 'HomeController@logout')->name('logout');
    Route::post('/set_favorite_streamer', 'HomeController@set_favorite_streamer')->name('set_favorite_streamer');
    Route::get('/view_favorite_streamer', 'HomeController@view_favorite_streamer')->name('view_favorite_streamer');
});

Route::any('/twitch_webhook/{type}', 'HomeController@twitch_webhook')->name('twitch_webhook');

