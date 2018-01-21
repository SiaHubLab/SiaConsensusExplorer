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

Route::get('/block/{height}', 'ExplorerController@getBlock');
Route::post('/blocks', 'ExplorerController@getBlocks');
Route::get('/hash/{hash}', 'ExplorerController@getHash');
Route::get('/latest', 'ExplorerController@getLatest');
Route::get('/search/{search}', 'ExplorerController@getSearchResults');
Route::get('/miner/{hash}/{block}', 'ExplorerController@miner');

Route::get('/blocks/distribution/{blocks}', 'ExplorerController@getBlocksDistribution');


Route::get('/health/main', 'HealthController@main');
Route::get('/health/endpoints', 'HealthController@endpoints');