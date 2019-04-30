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


use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

use App\Models\Pos\Member;
use App\Models\Pos\StoreGoodsSku;
use App\Models\Pos\Category;
use App\Models\Pos\Goods;
use App\Models\Pos\OrderGoods;
use App\Models\Pos\ShiftLog;
use App\Models\Pos\Bank;
use App\Models\Pos\Region;
use App\Models\Pos\ServerOrder;
use App\Models\Pos\GoodsSku;
use App\Models\Pos\GeneralLog;
use App\Models\Pos\GoodsImport;


/**
 * 收银交易控制器
 */
class ExcelController extends Controller
{
    public function index(Request $req)
    {
    	if($req->isMethod('POST'))
    	{
    		// 保存文件，读取文件
			$tag = "file";
			$local_id = $req->get('local_id');
			if(empty($_FILES['file']))
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
			if (move_uploaded_file($_FILES[$tag]['tmp_name'], $uploadfile)) {
			  // return response()->json(['code'=>1, 'error_code'=>0, 'message'=>'success', 'data'=>['image_path'=>"http://{$_SERVER['HTTP_HOST']}/img/{$new_file}"]]);
				$this->importExcel($uploadfile, $local_id);
			} else {
			  return response()->json(['code'=>0, 'error_code'=>101, 'message'=>'upload failed', 'data'=>[]]);
			}

			//return response()->json(['code'=>1, 'error_code'=>102, 'message'=>"oops, something went wrong", 'data'=>[]]);
    	}
        // 数据导入
        return view('pos.excel.index');
    }


    /**
     * 导入
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    private function importExcel($path, $store_id)
    {
    	$store_info = User::where(['local_id'=>$store_id])->first();
    	if(is_null($store_info))
    	{
    		// 店铺信息不存在，报错
    	}

    	// 数据开始行
    	$start_line = 3;
    	// 默认插入完全成功，遇到错误 变false
    	$is_success = true;

    	// 总行数
    	$total = 0;
    	// 出错行数
  		$error_num = 0;
    	// 新建行数
    	$create_num = 0;
    	// 更新行数
    	$update_num = 0;
    	// 提示信息
    	$message = [];
    	$error_num  = 0;
    	
		$reader = ReaderFactory::create(Type::XLSX); // for XLSX files

		$reader->open($path);


		$catlist = Category::all('name');
    	$tmplist = [];
    	foreach ($catlist as $key => $value) {
    		$tmplist[] = $value->name;
    	}

    	$abc = array_flip($tmplist);

		// 事务
		
		$sheet_num = 0;
		$fields = ['商品名称','商品条码','商品分类','计价方式','商品规格', '商品单位','进货价','零售价','起始库存','库存预警','货架号','过期时间','是否上架','是否快捷'];
		foreach ($reader->getSheetIterator() as $sheet) {
			$sheet_num +=1;
			if($sheet_num > 1)
			{
				// 返回
			}

			$cur = 0;
		    foreach ($sheet->getRowIterator() as $row) {
		    	// 统计信息
		    	$total +=1;

		    	$cur += 1;
		    	if($cur < 4)
		    	{
		    		// 前三行为模板内容
		    		continue;
		    	}

		    	// exit(json_encode($row)." row num = {$cur}");
		    	// 校验数据
		    	if(empty($row[1]) && empty($row[2]) && empty($row[3]))
		    	{
		    		break;
		    	}

		    	$ret = $this->validateRow($row, $cur);
		    	if($ret!==true)
		    	{
		    		// 组装错误信息
		    		$message[] = $ret;
		    		continue;
		    	}
		        
		        // 写入数据
		        $is_exist = GoodsImport::where(['goods_sn'=>$row[2],'user_id'=>$store_id])->first();
		        if(!is_null($is_exist))
		        {
		        	
					$is_exist->user_id           = $store_id; // usr_id
					$is_exist->store_code        = $store_info->store_code;	// store_code
					$is_exist->goods_name        = $row[1];
					
					$is_exist->goods_sn          = $row[2];
					$is_exist->cat_id            = $abc[$row[3]] + 1;   // cat_id
					$is_exist->type              = $row[4];
					$is_exist->goods_picture     = ""; // 商品图片
					$is_exist->spec              = $row[5];
					$is_exist->create_time       = time();
					$is_exist->unit              = $row[6];
					$is_exist->cost_price        = $row[7];
					$is_exist->shop_price        = $row[8];
					$is_exist->repertory         = $row[9];
					$is_exist->repertory_caution = $row[10];
					$is_exist->place_code        = $row[11];
					
					$is_exist->staleTime         = strtotime($row[12]->format('Y-m-d'));
					$is_exist->custom            = "1";
					$is_exist->is_forsale        = $row[13];
					$is_exist->sale_time         = time();
					$is_exist->is_short          = $row[14];
					$is_exist->short_time        = time();
					$is_exist->check             = "0";
					$is_exist->last_modified     = time();


		        	// 保存，保存成功后，更新记
		        	$save_rest = $is_exist->save();
		        	if($save_rest !== false)
		        	{
		        		$update_num +=1;	
		        	} else {
		        		// 保存出错
		        		$error_num +=1;
		        	}

		        } else {
		        	$new_rec = new GoodsImport();
					$new_rec->user_id           = $store_id; // usr_id
					$new_rec->store_code        = $store_info->store_code;	// store_code
					$new_rec->goods_name        = $row[1];
					
					$new_rec->goods_sn          = $row[2];
					$new_rec->cat_id            = $abc[$row[3]] + 1;   // cat_id
					$new_rec->type              = $row[4];
					$new_rec->goods_picture     = ""; // 商品图片
					$new_rec->spec              = $row[5];
					$new_rec->create_time       = time();
					$new_rec->unit              = $row[6];
					$new_rec->cost_price        = $row[7];
					$new_rec->shop_price        = $row[8];
					$new_rec->repertory         = $row[9];
					$new_rec->repertory_caution = $row[10];
					$new_rec->place_code        = $row[11];
					$new_rec->staleTime         = strtotime($row[12]->format('Y-m-d'));
					$new_rec->custom            = "1";
					$new_rec->is_forsale        = $row[13];
					$new_rec->sale_time         = time();
					$new_rec->is_short          = $row[14];
					$new_rec->short_time        = time();
					$new_rec->check             = "0";
					$new_rec->last_modified     = time();

		        	$create_rest = $new_rec->save();
		        	if($create_rest !== false)
		        	{
		        		$create_num +=1;
		        	} else {
						$error_num +=1;
		        	}

		        	
		        }
		    }
		}

		$reader->close();

		$total -=4;
		if(empty($message))
		{
			exit(json_encode(['code'=>1,'message'=>"success 共处理 {$total} 条，新增 {$create_num} 条， 更新 {$update_num} 条, 出错 {$error_num} 条"]));
		} else {
			exit(json_encode(['code'=>0,'message'=>'fail', 'data' => $message]));
		}
		// 返回结果
    }

    /**
     * 验证数据， 正确 true ,错误返回错误提示 row_index  col_index
     * @param  [type] $row [description]
     * @return [type]      [description]
     */
    private function validateRow($row, $row_num)
    {
    	$col = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q'];
    	$row_num +=1;

    	$message = "";
    	$catlist = Category::all('name');
    	$tmplist = [];
    	foreach ($catlist as $key => $value) {
    		$tmplist[] = $value->name;
    	}

    	$abc = array_flip($tmplist);

    	// 验证规则
    	// 商品分类必须是指定
    	if(in_array($row[3], $tmplist) !=true)
    	{
    		$message.="第{$row_num} 行, 第 {$col[3]} 列;";
    	}

    	// 计价方式，是否上架，是否快捷只能是 0 或者 1
    	if($row[4] != 0 && $row[4] != 1)
    	{
    		$message.="第{$row_num} 行, 第 {$col[4]} 列;";	
    	}

    	// 进货价零售价 库存和预警只能是 数字
    	if(is_float($row[7]) !== true&& is_integer($row[7])!==true)
    	{
    		$message.="第{$row_num} 行, 第 {$col[6]} 列;";	
    	}

    	if(is_float($row[8]) !== true && is_integer($row[8])!==true)
    	{
    		$message.="第{$row_num} 行, 第 {$col[7]} 列;";	
    	}

    	if(is_integer($row[9]) !== true)
    	{
    		$message.="第{$row_num} 行, 第 {$col[9]} 列;";	
    	}

    	if(is_integer($row[10]) !== true)
    	{
    		$message.="第{$row_num} 行, 第 {$col[10]} 列;";	
    	}

    	// 计价方式，是否上架，是否快捷只能是 0 或者 1
    	if($row[13] != 0 && $row[13] != 1)
    	{
    		$message.="第{$row_num} 行, 第 {$col[13]} 列;";	
    	}

    	// 计价方式，是否上架，是否快捷只能是 0 或者 1
    	if($row[14] != 0 && $row[14] != 1)
    	{
    		$message.="第{$row_num} 行, 第 {$col[14]} 列;";	
    	}

    	if($message != "")
    	{
    		return $message;
    	}

    	return true;

    	
    }

}
