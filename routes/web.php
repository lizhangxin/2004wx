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

//Route::get('/add','TextController@add');
//Route::any('/index','Weixin\WeixinController@index');
Route::any('/checkSignature','Weixin\WeixinController@checkSignature');
Route::get('/getToken','Weixin\WeixinController@getToken');
Route::get('/getweather','Weixin\WeixinController@getweather');
Route::post('/createMenu','Weixin\WeixinController@createMenu');

//TEST 路由分组
Route::prefix('/test')->group(function (){
    Route::get('/guzzle1',"TestController@guzzle1");



});
