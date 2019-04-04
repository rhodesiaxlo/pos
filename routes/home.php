<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-18
 * Time: 11:13
 */

Route::get('index/login', 'IndexController@login')->name('home.index.login');
Route::get('index/upload', 'IndexController@upload')->name('home.index.upload');
Route::get('index/storetovisit', 'IndexController@storetovisit')->name('home.index.storetovisit');
Route::get('index/mark', 'IndexController@mark')->name('home.index.mark');



