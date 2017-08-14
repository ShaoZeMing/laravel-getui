<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'GeTui\App\Http\Controllers'], function () {
	Route::get('msg/list', 'MessageApiController@index');
});


// $app->group(['prefix' => 'admin','namespace' => 'GeTui\App\Http\Controllers', 'middleware' => ['web', 'admin:admin']], function () {

// 	$app->get('message', 'MessageController@index');                     //通知管理
// 	$app->get('message/search', 'MessageController@search');             //通知用户筛选
// 	$app->get('message/pushCreate', 'MessageController@pushCreate');     //通知添加数据页
// 	$app->post('message/pushAdd', 'MessageController@pushAdd');          //通知插入数据
// 	$app->post('message/saveStatus', 'MessageController@saveStatus');    //通知状态修改
// 	$app->get('messagePush', 'MessagePushController@index');                     //消息管理
// 	$app->get('messagePush/index', 'MessagePushController@index');               //消息消息管理
// 	$app->get('messagePush/search', 'MessagePushController@search');             //消息用户筛选
// 	$app->get('messagePush/newsCreate', 'MessagePushController@newsCreate');     //消息添加数据页
// 	$app->post('messagePush/newsAdd', 'MessagePushController@newsAdd');          //消息插入数据

// });