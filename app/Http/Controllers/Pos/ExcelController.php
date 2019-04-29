<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\AbnormalTransactionLog;
use App\Models\Pos\Prepayment;
use App\Models\Pos\Postpayment;

use App\Models\Pos\OutflowLog;
use App\Models\Pos\OutflowPrepayment;
use App\Models\Pos\User;
use App\Models\Pos\Order;

use DB;
use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;
use  App\Models\Admin\AdminUser;
use Illuminate\Support\Facades\Log;

/**
 * 收银交易控制器
 */
class ExcelController extends Controller
{
    public function index(Request $req)
    {
    	if($req->isMethod('POST'))
    	{
    		exit(json_encode($_FILES['excel']));
    		// 保存文件，读取文件
			$tag = "excel";
			if(empty($_FILES['excel']))
			{
				return response()->json(['code'=>0, 'error_code'=>100, 'message'=>"excel information not found", 'data'=>[]]);

			}

			$uploaddir = 'app/';
			$filename = $_FILES[$tag]['name'];
			$ext_arr = explode(".", $filename);
			if(!is_array($ext_arr)||sizeof($ext_arr)<2)
			{
				return response()->json(['code'=>0, 'error_code'=>103, 'message'=>"no extention was found", 'data'=>[]]);
			}

			$ext = $ext_arr[sizeof($ext_arr)-1];

			$new_file = date('Ymd').time().rand(10,99999).'.'.$ext;
			$uploadfile = './excel/' .$new_file;
			// 保存文件名
			if (move_uploaded_file($_FILES[$tag]['name'], $uploadfile)) {
			  return response()->json(['code'=>1, 'error_code'=>0, 'message'=>'success', 'data'=>['image_path'=>"http://{$_SERVER['HTTP_HOST']}/img/{$new_file}"]]);
			} else {
			  return response()->json(['code'=>0, 'error_code'=>101, 'message'=>'upload failed', 'data'=>[]]);
			}

			//return response()->json(['code'=>1, 'error_code'=>102, 'message'=>"oops, something went wrong", 'data'=>[]]);
    	}
        // 数据导入
        return view('pos.excel.index');
    }

}
