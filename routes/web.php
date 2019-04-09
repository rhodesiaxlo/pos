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

Route::get('/', function () {
	return redirect('/admin/index');
    //return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::group(['prefix' => 'salesman', 'namespace' => 'Salesman','middleware'=>['auth:admin', 'menu', 'authAdmin']], function () {
    require_once 'salesman.php';
});

Route::group(['prefix' => 'pos', 'namespace' => 'Pos','middleware'=>['auth:admin', 'menu', 'authAdmin']], function () {
    require_once 'pos.php';
});


Route::group(['prefix' => 'dashboard', 'namespace' => 'Dashboard','middleware'=>['auth:admin', 'menu', 'authAdmin']], function () {
    require_once 'dashboard.php';
});
Route::group(['prefix' => 'videos', 'namespace' => 'Videos','middleware'=>['auth:admin', 'menu', 'authAdmin']], function () {
    require_once 'tvwall.php';
});
