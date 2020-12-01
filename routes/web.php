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
Route::get('/responseMsg','Weixin\WeixinController@responseMsg');
Route::get('/custom','Weixin\WeixinController@custom');
//TEST 路由分组
Route::prefix('/test')->group(function (){
    Route::get('/guzzle1',"TestController@guzzle1");
});

//小程序接口
Route::prefix('/api')->group(function (){
    Route::get('/test',"Weixin\ApiController@test");
});

Route::prefix('/wx')->group(function (){
    Route::any('/login','Weixin\XcxController@login');//登录
    Route::get('/goods','Weixin\XcxController@goods');//商品数据
    Route::get('/detail','Weixin\XcxController@detail');//商品详情
    Route::get('/add_fav','Weixin\XcxController@add_fav');//收藏
    Route::get('/no_fav','Weixin\XcxController@no_fav');//取消收藏
    Route::get('/cart','Weixin\XcxController@cart');//加入购物车
    Route::get('/cartlist','Weixin\XcxController@cartlist');//加入购物车


});

