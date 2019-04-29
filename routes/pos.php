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
Route::any('transaction/index/{excel?}/{date?}','TransactionController@index')->name('pos.transaction.index');
Route::any('transaction/depositconfirm','TransactionController@depositConfirm')->name('pos.transaction.depositconfirm');
Route::any('transaction/payment/{excel?}/{date?}','TransactionController@payment')->name('pos.transaction.payment');
Route::any('transaction/withdrawconfirm/{excel?}/{date?}','TransactionController@withdrawConfirm')->name('pos.transaction.withdrawconfirm');

Route::any('transaction/outflow','TransactionController@outflow')->name('pos.transaction.outflow');
Route::any('transaction/firstcheck','TransactionController@firstCheck')->name('pos.transaction.firstcheck');
Route::any('transaction/recheck','TransactionController@reCheck')->name('pos.transaction.recheck');
Route::any('transaction/export','TransactionController@export')->name('pos.transaction.export');


Route::any('excel/index','ExcelController@index')->name('pos.excel.index');
/*** REST 重构使用 ****/
/*
// 收银对账
Route::any('deposittx/index','DepositTxController@index')->name('pos.deposittx.index');
Route::any('deposittx/firstcheck','DepositTxController@firstCheck')->name('pos.deposittx.firstcheck');
Route::any('deposittx/recheck','DepositTxController@reCheck')->name('pos.deposittx.recheck');
Route::any('deposittx/export','DepositTxController@export')->name('pos.deposittx.export');

// 打款
Route::any('writeoff/index','WriteOffController@index')->name('pos.writeoff.index');
Route::any('writeoff/outflow','WriteOffController@outflow')->name('pos.writeoff.outflow');

// 结算对账
Route::any('withdrawtx/index','WithdrawTxController@index')->name('pos.withdrawtx.index');
Route::any('withdrawtx/firstcheck','WithdrawTxController@firstCheck')->name('pos.withdrawtx.firstcheck');
Route::any('withdrawtx/recheck','WithdrawTxController@reCheck')->name('pos.withdrawtx.recheck');
Route::any('withdrawtx/export','WithdrawTxController@export')->name('pos.withdrawtx.export');

// excel 处理页面
Route::any('excel/export','ExcelController@export')->name('pos.excel.index');
*/

/*** REST 重构使用 ****/




