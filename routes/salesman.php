<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-18
 * Time: 11:13
 */
Route::get('sales/index','SalesmanController@index')->name('salesman.sales.index');
Route::post('sales/index','SalesmanController@index')->name('salesman.sales.index');
Route::any('sales/create/{id?}','SalesmanController@create')->name('salesman.sales.create');
Route::get('sales/edit/{id?}','SalesmanController@edit')->name('salesman.sales.edit');
Route::post('sales/edit/','SalesmanController@edit')->name('salesman.sales.edit');
Route::put('sales/edit/','SalesmanController@edit')->name('salesman.sales.edit');


Route::delete('sales/destroy/{id}','SalesmanController@destroy')->name('salesman.sales.destroy');
Route::get('sales/{id}/details','SalesmanController@details')->name('salesman.sales.details');
Route::post('sales/storelist','SalesmanController@StoreList')->name('salesman.sales.storelist');
Route::post('sales/store','SalesmanController@store')->name('salesman.sales.store');
Route::post('sales/update',['as'=>'salesman.sales.update','user'=>'SalesmanController@update']);
Route::resource('sales','SalesmanController');
/**
 * 门店
*/
Route::any('store/index','StoreController@index')->name('salesman.store.index')->middleware('throttle:500');
Route::any('store/create/{id?}','StoreController@create')->name('salesman.store.create');
Route::post('store/store','StoreController@store')->name('salesman.store.store');
Route::get('store/edit/{id?}','StoreController@edit')->name('salesman.store.edit');
Route::post('store/edit/','StoreController@edit')->name('salesman.store.edit');


Route::post('store/update','StoreController@update')->name('salesman.store.update');
Route::any('store/destroy/{id?}','StoreController@destroy')->name('salesman.store.destroy');
Route::get('store/details/{id?}','StoreController@details')->name('salesman.store.details');
//图片上传
Route::any('store/uplode','StoreController@uplode')->name('salesman.store.uplode');
Route::resource('store','StoreController');
Route::delete('order/destroy/{id}','OrderController@destroy')->name('salesman.order.destroy');
Route::match(['post','get'],'order/index','OrderController@index')->name('salesman.order.index');