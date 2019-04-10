<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\User;
use App\Models\Pos\Bank;


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
        $userlist = User::with('creator')->with('bank')->paginate(env('RECORD_PERPAGE'));

    	return view('pos.store.index')->with('users', $userlist);
    }

    /**
     * 创建店铺
     * @param Request $req [description]
     */
    public function add(Request $req)
    {
        if($req->isMethod('post'))
        {
            // todo 
            // 
            exit(json_encode(['code'=>0, 'message'=>"error",'data'=>json_encode($_POST)]));
            return redirect('pos/store/index')->withErrors("添加失败");
            return redirect('pos/store/index')->withSuccess('添加成功');
        }

        $banklist = Bank::all();
    	return view('pos.store.add')->with('banklist', $banklist);
    }

    public function edit(Request $req)
    {
        if($req->isMethod('post'))
        {
            // todo 
            // 
            return redirect('pos/store/index')->withErrors("更新失败");
            return redirect('pos/store/index')->withSuccess('更新成功');
        }
    	return view('pos.store.update');
    }

    public function del(Request $req)
    {
        if($req->isMethod('post'))
        {
            // todo 
            // 
            return redirect('pos/store/index')->withErrors("删除失败");
            return redirect('pos/store/index')->withSuccess('删除成功');
        }
    	return view('pos.store.del');
    }
}
