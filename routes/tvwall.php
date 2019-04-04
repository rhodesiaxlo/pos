<?php

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
Route::match(['get','post'],'tvwall/index','TvWallController@index')->name('videos.tvwall.index');
Route::get('tvwall/create/{id}','TvWallController@create')->name('videos.tvwall.create');
Route::post('tvwall/store','TvWallController@store')->name('videos.tvwall.store');
Route::post('tvwall/AjaxIndexs','TvWallController@AjaxIndexs')->name('videos.tvwall.AjaxIndexs');
Route::get('tvwall/edit/{id}','TvWallController@edit')->name('videos.tvwall.edit');
Route::post('tvwall/update','TvWallController@update')->name('videos.tvwall.update');
Route::delete('tvwall/destroy/{id}','TvWallController@destroy')->name('videos.tvwall.destroy');
Route::resource('tvwall','TvWallController');
