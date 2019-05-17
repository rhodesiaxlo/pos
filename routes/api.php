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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group(['namespace' => 'Api','middleware' => ['api']], function () {
    Route::match(['get','post'],'apiadmin/getxy','ApiAdminController@getXyCoordinate')->name('api.apiadmin.getxy');
    Route::match(['get','post'],'apiadmin/regionmessage','ApiAdminController@RegionMessage')->name('api.apiadmin.regionmessage');
    Route::match(['get','post'],'apiadmin/index','ApiAdminController@index')->name('api.apiadmin.index');
    Route::match(['get','post'],'apiadmin/storeregion','ApiAdminController@StoreRegion')->name('api.apiadmin.storeregion');

    // 跑店接口
    Route::match(['get','post'],'apiadmin/agentlogin','ApiAdminController@agentLogin')->name('api.apiadmin.agentlogin');
    Route::match(['get','post'],'apiadmin/storetovisit','ApiAdminController@storeToVisit')->name('api.apiadmin.storetovisit');
    Route::match(['get','post'],'apiadmin/mark','ApiAdminController@mark')->name('api.apiadmin.mark');
    Route::match(['get','post'],'apiadmin/savestore','ApiAdminController@saveStore')->name('api.apiadmin.savestore');
    Route::match(['get','post'],'apiadmin/wximageupload','ApiAdminController@wximageupload')->name('api.apiadmin.wximageupload');
    Route::match(['get','post'],'apiadmin/getstoreinfo','ApiAdminController@getstoreinfo')->name('api.apiadmin.getstoreinfo');
    Route::match(['get','post'],'apiadmin/getstorelist','ApiAdminController@getstorelist')->name('api.apiadmin.getstorelist');

    // pos 机接口
    Route::match(['get','post'],'apipos/index','ApiPosController@index')->name('api.apipos.index');
    Route::match(['get','post'],'apipos/poslogin','ApiPosController@posLogin')->name('api.apipos.poslogin');
    Route::match(['get','post'],'apipos/pullhistorydata','ApiPosController@pullHistoryData')->name('api.apipos.pullhistorydata');
    Route::match(['get','post'],'apipos/ccpc1402','ApiPosController@ccpc1402')->name('api.apipos.ccpc1402');
    Route::match(['get','post'],'apipos/ccpc1811','ApiPosController@ccpc1811')->name('api.apipos.ccpc1811');
    Route::match(['get','post'],'apipos/ccpc1341','ApiPosController@ccpc1341')->name('api.apipos.ccpc1341');
    Route::match(['get','post'],'apipos/ccpc1410','ApiPosController@ccpc1410')->name('api.apipos.ccpc1410');
    Route::match(['get','post'],'apipos/ccpc1408','ApiPosController@ccpc1408')->name('api.apipos.ccpc1408');

    Route::match(['get','post'],'apipos/getskugoods','ApiPosController@getSkuGoods')->name('api.apipos.getskugoods');
    Route::match(['get','post'],'apipos/importnotify','ApiPosController@importNotify')->name('api.apipos.importnotify');
    
    

    //回调接口
    Route::match(['get','post'],'apipos/ccpcnotify','ApiPosController@ccpcNotify')->name('api.apipos.ccpcnotify');
    Route::match(['get','post'],'apipos/pullrange','ApiPosController@pullRange')->name('api.apipos.pullrange');

    Route::match(['get','post'],'apipos/pullbillfromccpc','ApiPosController@pullBillFromCcpc')->name('api.apipos.pullbillfromccpc');
    Route::match(['get','post'],'apipos/generatepreapi','ApiPosController@generatePreApi')->name('api.apipos.generatepreapi');
    Route::match(['get','post'],'apipos/generateoutflowprepayment','ApiPosController@generateOutflowPrepayment')->name('api.apipos.generateoutflowprepayment');
    
    Route::match(['get','post'],'apipos/banklist','ApiPosController@banklist')->name('api.apipos.banklist');
    Route::match(['get','post'],'apipos/province','ApiPosController@province')->name('api.apipos.province');
    Route::match(['get','post'],'apipos/storelist','ApiPosController@storelist')->name('api.apipos.storelist');
    Route::match(['get','post'],'apipos/test','ApiPosController@test')->name('api.apipos.test');
    Route::match(['get','post'],'apipos/getorderstatus','ApiPosController@getOrderStatus')->name('api.apipos.getorderstatus');
    
    
    Route::match(['get','post'],'apipos/city','ApiPosController@city')->name('api.apipos.city');
    Route::match(['get','post'],'apipos/area','ApiPosController@area')->name('api.apipos.area');
    Route::match(['get','post'],'apipos/checkupdate','ApiPosController@checkUpdate')->name('api.apipos.checkupdate');
    

    Route::match(['get','post'],'apipos/syncmember','ApiPosController@syncMember')->name('api.apipos.syncmember');
    Route::match(['get','post'],'apipos/syncgoods','ApiPosController@syncGoods')->name('api.apipos.syncgoods');
    Route::match(['get','post'],'apipos/syncorder','ApiPosController@syncOrder')->name('api.apipos.syncorder');
    Route::match(['get','post'],'apipos/syncshiftlog','ApiPosController@syncShiftLog')->name('api.apipos.syncshiftlog');
    Route::match(['get','post'],'apipos/syncuser','ApiPosController@syncUser')->name('api.apipos.syncuser');
    Route::match(['get','post'],'apipos/syncdata','ApiPosController@syncData')->name('api.apipos.syncdata');

    // 中金回调函数
    Route::match(['get','post'],'notify/index','NotifyController@index')->name('api.notify.index');

    Route::resource('apiadmin','ApiAdminController');
});
