<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Videos\TvRegin;
use DB;
use Carbon\Carbon;
use Event;
use App\Events\permChangeEvent;


class IndexController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * 登录接口
     * @return [type] [description]
     */
    public function login(Request $request)
    {
        exit(json_encode($request->get()));
        exit(json_encode($_REQUEST));
        exit(' login interface');
    }

    /**
     * 店铺访问列表
     * @return [type] [description]
     */
    public function storeToVisit()
    {
        exit(' store to visit interface');
    }

    /**
     * 打卡
     * @return [type] [description]
     */
    public function mark()
    {
        exit(' 打卡接口');
    }   

    /**
     * 图片上传接口
     * @return [type] [description]
     */
    public function upload()
    {
        exit('upload interface');
    }

    public function test()
    {
        exit(' test interface');
    }

}
