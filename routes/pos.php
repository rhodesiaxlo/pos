<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-18
 * Time: 11:13
 */
/**
 * 门店
*/
Route::any('store/index','StoreController@index')->name('pos.store.index')->middleware('throttle:500');
Route::any('store/add/{id?}','StoreController@add')->name('pos.store.add');
Route::any('store/del/{id?}','StoreController@del')->name('pos.store.del');
Route::any('store/edit/{id?}','StoreController@edit')->name('pos.store.edit');

/**
 * 交易页面
 */
Route::any('transaction/index','TransactionController@index')->name('pos.transaction.index');
Route::any('transaction/depositconfirm','TransactionController@depositConfirm')->name('pos.transaction.depositconfirm');
Route::any('transaction/payment','TransactionController@payment')->name('pos.transaction.payment');
Route::any('transaction/withdrawconfirm','TransactionController@withdrawConfirm')->name('pos.transaction.withdrawconfirm');

Route::any('transaction/outflow','TransactionController@outflow')->name('pos.transaction.outflow');
Route::any('transaction/firstcheck','TransactionController@firstCheck')->name('pos.transaction.firstcheck');
Route::any('transaction/recheck','TransactionController@reCheck')->name('pos.transaction.recheck');


