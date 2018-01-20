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

Route::get('/', 'MainController@index');
Route::get('/api-health', 'MainController@index');
Route::get('/hash/{hash_id}', 'MainController@index');
Route::get('/block/{hash_id}', 'MainController@index');
