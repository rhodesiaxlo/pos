<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\User;
use App\Models\Pos\Bank;

use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;
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
        $where[] = array("deleted", 0);
        $search = [];
        $key = "";
        $value = "";



        if($req->isMethod('POST'))
        {
            $type = $req->get('class');
            $value = $req->get('Keyword');
            $search['type'] = $type;
            $search['value'] = $value;
            if(!empty($value))
            {

                switch ($type) {
                    case 0:
                        // 店铺名称
                        $key = "store_name";
                        $where[] = array("store_name", "like", "%".$value."%");
                        break;
                    case 1:
                        // 店主姓名
                        $key = "realname";
                        $where[] = array("realname", "like", "%".$value."%");
                        break;
                    case 2:
                        // 商户编码
                        $key = "store_code";
                        $where[] = array("store_code", "like", "%".$value."%");
                        break;
                    case 3:
                        $key = "business_licence_no";
                        $where[] = array("business_licence_no", "like", "%".$value."%");
                        break;
                    case 4:
                        // 店铺地址
                        $key = "address";
                        $where[] = array("address", "like", "%".$value."%");
                        break;
                    case 5:
                        $key = "phone";
                        $where[] = array("phone", "like", "%".$value."%");
                        break;
                    case 6:
                        $key = "account_name";
                        $where[] = array("account_name", "like", "%".$value."%");
                        break;
                    case 7:
                        $key = "account_no";
                        $where[] = array("account_no", "like", "%".$value."%");

                        break;
                    case '8':
                        $key = "bank_name";
                        $bankinfo = Bank::where('name', "like", "%".$value."%")->first();
                        if(!is_null($bankinfo))
                        {
                            $where['bank_id'] = $bankinfo->id;    
                        } else {
                            $where['bank_id'] = -1;    

                        }

                        break;
                    default:
                        break;
                }
            }

          // exit(json_encode($userlist));
        }

        if(empty($key))
        {
            $userlist = User::with('creator')->with('bank')->where($where)->orderby('create_time', "desc")->paginate(env('RECORD_PERPAGE'));
        } else {
            $userlist = User::with('creator')->with('bank')->where($where)->orderby('create_time', "desc")->paginate(env('RECORD_PERPAGE'))->appends($key, $value);
        }
        
    	return view('pos.store.index')->with('users', $userlist)->with('search', $search);
    }

    /**
     * 创建店铺
     * @param Request $req [description]
     */
    public function add(Request $req)
    {
        if($req->isMethod('POST'))
        {
            // 去重 uname
            $is_exist = User::where(['uname'=>$req->get('uname')])->first();
            if(!is_null($is_exist))
            {
                return redirect('pos/store/index')->withSuccess('添加失败，登录名已存在');
            }

            $bu_no_exist = User::where(['business_licence_no'=>$req->get('number')])->first();
            if(!is_null($bu_no_exist))
            {
                return redirect('pos/store/index')->withSuccess('添加失败，营业执照已存在');
            }

            $loginTokenName = Auth::guard('admin')->getName();
            $operator=Session::get($loginTokenName);

            $userinfo = new User();
            $userinfo->province_id         = $req->get('province');
            $userinfo->city_id             = $req->get('city');
            $userinfo->area_id             = $req->get('county');
            $userinfo->address             = $req->get('address');
            $userinfo->store_code          = $req->get('store_code');
            $userinfo->realname            = $req->get('name');
            $userinfo->phone               = $req->get('phone');
            $userinfo->uname               = $req->get('username');
            $userinfo->password            = $req->get('userSub');
            $userinfo->bank_id             = $req->get('place');
            $userinfo->account_name        = $req->get('amuName');
            $userinfo->account_no          = $req->get('amuNum');
            $userinfo->business_licence_no = $req->get('number');
            $userinfo->store_name          = $req->get('nameStort');
            $userinfo->rank                = 0;
            $userinfo->create_time         = time();
            $userinfo->is_active              = trim($req->get('status'))=="启用"?1:0;

            $userinfo->created_by          = $operator;
            $result                        = $userinfo->save();

            if($result === false)
            {
                return redirect('pos/store/index')->withErrors("添加失败");
            } else {
                return redirect('pos/store/index')->withSuccess('添加成功');
            }

            // exit(json_encode(['code'=>0, 'message'=>"error",'data'=>json_encode($_POST)]));
            // return redirect('pos/store/index')->withErrors("添加失败");
            // return redirect('pos/store/index')->withSuccess('添加成功');
        }

        $banklist = Bank::all();
        $store_code = User::getRandomStoreCode();
    	return view('pos.store.add')->with('banklist', $banklist)->with('store_code', $store_code);
    }

    public function edit(Request $req)
    {
        if($req->isMethod('POST'))
        {
            $local_id = $req->get('local_id');


            $userinfo = User::find($local_id);

            $type = $req->get('type');
            if(empty($type))
            {
                $userinfo->province_id         = $req->get('province');
                $userinfo->city_id             = $req->get('city');
                $userinfo->area_id             = $req->get('county');
                $userinfo->address             = $req->get('address');
                $userinfo->realname            = $req->get('name');
                $userinfo->phone               = $req->get('phone');
                $userinfo->uname               = $req->get('uname');
                $userinfo->password            = $req->get('password');
                $userinfo->bank_id             = $req->get('place');
                $userinfo->account_name        = $req->get('account_name');
                $userinfo->account_no          = $req->get('account_no');
                $userinfo->business_licence_no = $req->get('number');
                $userinfo->store_name          = $req->get('nameStort');
                $userinfo->is_active              = trim($req->get('staus'))=="启用"?1:0;
            } else {
                $userinfo->is_active              = $req->get('status');
                $result                        = $userinfo->save();
                if($result !== false)
                {
                    exit(json_encode(['code'=>1, 'message'=>"success"]));
                } else {
                    exit(json_encode(['code'=>0, 'message'=>"更新失败", 'error_code'=>1000]));
                }
            }

            $userinfo->rank                = 0;
            $result                        = $userinfo->save();

            if($result === false)
            {
                return redirect('pos/store/index')->withErrors("添加失败");
            } else {
                return redirect('pos/store/index')->withSuccess('添加成功');
            }

            // return redirect('pos/store/index')->withErrors("更新失败");
            // return redirect('pos/store/index')->withSuccess('更新成功');
        }

        $local_id  = $req->get('id');
        $info = User::find($local_id);
    	return view('pos.store.update')->with('userinfo', $info);
    }

    public function del(Request $req)
    {
        if($req->isMethod('POST'))
        {
            // todo 
            $user_id = $req->get('local_id');
            if(empty($user_id)||intval($user_id)===false)
            {
                return redirect('pos/store/index')->withErrors("删除失败. ");
            }

            $info = User::find($user_id);
            if(empty($info))
            {
                return redirect('pos/store/index')->withErrors("删除失败 .. ");
            }

            $info->deleted = 1;
            $result = $info->save();
            if($result === false)
            {
                return redirect('pos/store/index')->withErrors("删除失败 .. ");
            } else {
                return redirect('pos/store/index')->withSuccess('删除成功');
            }

        }
        
        $user_id = $req->get('id');
        if(empty($user_id)||intval($user_id)===false)
        {
            return redirect('pos/store/index')->withErrors("删除失败. ");
        }

        $info = User::find($user_id);
        if(empty($info))
        {
            return redirect('pos/store/index')->withErrors("删除失败 .. ");
        }

        $info->deleted = 1;
        $result = $info->save();
        if($result === false)
        {
            return redirect('pos/store/index')->withErrors("删除失败 .. ");
        } else {
            return redirect('pos/store/index')->withSuccess('删除成功');
        }


        exit(json_encode(['code'=>0, 'message'=>"error",'data'=>json_encode($_POST)]));

    	//return view('pos.store.del');
    }
}
