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
Route::get('sync/index', 'SyncController@index');
Route::get('sync/laudus/products/{code?}', 'SyncController@syncLaudusProducts');
Route::get('sync/laudus/stock', 'SyncController@syncLaudusStock');
Route::get('sync/wc/products/{take?}', 'SyncController@syncWCProducts');
Route::get('sync/wc/product/{sku}', 'SyncController@syncWCProductsBySku');
Route::get('sync/stock', 'SyncController@syncStock');
Route::get('sync/orders', 'SyncController@getOrders');
Route::get('sync/config', 'SyncController@config');
Route::get('users/config', 'UserController@config');
Route::get('users', 'UserController@index');
Route::get('orders', 'OrderController@index');
Route::get('orders/config', 'OrderController@config');
Route::get('orders/test-wp', 'OrderController@wpConnection');
Route::get('orders/list', 'OrderController@getOrders');
Route::group(['middleware' => 'auth'], function(){
});