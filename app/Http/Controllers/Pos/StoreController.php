<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


/**
 * 便利店店铺类
 */
class StoreController extends Controller
{
    
    /**
     * 店铺列表页面
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function index(Request $req){
    	// 搜索，分页
    	return view('pos.store.index');
    }

    /**
     * 创建店铺
     * @param Request $req [description]
     */
    public function add(Request $req)
    {
    	return view('pos.store.add');
    }

    public function edit(Request $req)
    {
    	return view('pos.store.update');
    }

    public function del(Request $req)
    {
    	return view('pos.store.del');
    }
}
