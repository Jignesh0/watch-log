<?php

use Illuminate\Support\Facades\Route;

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

Route::get('logs', 'nineBrainz\logs\LogController@index')->name('logs');
Route::post('get-log-list', 'nineBrainz\logs\LogController@logList')->name('get-log-list');
