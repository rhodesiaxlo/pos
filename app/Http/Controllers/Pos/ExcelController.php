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

			if(trim($ext)!="xlsx")
			{
				return response()->json(['code'=>0, 'error_code'=>101, 'message'=>'模板不正确', 'data'=>[]]);	
			}

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

		    	// 校验数据 碰到空行，跳出循环
		    	if(empty($row[1]) && empty($row[2]) && empty($row[3]))
		    	{
		    		break;
		    	}

		    	$ret = $this->validateRow($row, $cur);
		    	if($ret!==true)
		    	{
		    		// 组装错误信息
		    		$message[] = $ret;
		    		$error_num+=1;
		    		// continue;
		    	}else {
		        
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
						
						if(strtolower( gettype($row[12])) == "object")
						{
							$obj = json_decode(json_encode($row[12], true));
            				$date = strval($obj->date);
							$is_exist->staleTime = strtotime($date);
						} else {
							$is_exist->staleTime         = strtotime(date('Y-m-d',$row[12]));
						}
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
						if(strtolower( gettype($row[12])) == "object")
						{
							$obj = json_decode(json_encode($row[12], true));
            				$date = strval($obj->date);
							$new_rec->staleTime = strtotime($date);
						} else {
							if(strtolower( gettype($row[12])) == "string" && empty($row[12]))
							{
								$row['12'] = 0;
							}

							$new_rec->staleTime         = strtotime(date('Y-m-d',$row[12]));
						}
						//$new_rec->staleTime         = strtotime(date('Y-m-d',$row[12]));
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
		}

		$reader->close();

		$total -=4;
		if(empty($message))
		{
			exit(json_encode(['code'=>1,'message'=>"success 共处理 {$total} 条，新增 {$create_num} 条， 更新 {$update_num} 条, 出错 {$error_num} 条"]));
		} else {
			exit(json_encode(['code'=>0,'message'=>"共处理 {$total} 条，新增 {$create_num} 条， 更新 {$update_num} 条, 出错 {$error_num} 条", 'data' => $message]));
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

        // 商品名称，飞控
    	if(empty($row[1])|| strlen($row[1])>40)
    	{
    		$message.="第{$row_num} 行, 第 {$col[1]} 列;";
    	}

        // 商品条码 非空
        // 全数字  ？？？
    	if(empty($row[2])|| strlen($row[2])>40)
    	{
    		$message.="第{$row_num} 行, 第 {$col[2]} 列;";
    	}

        // 商品分类 非空
        // 且要在分类栏目范围之内
    	if(empty($row[3]) || !in_array($row[3], $tmplist))
    	{
    		$message.="第{$row_num} 行, 第 {$col[3]} 列;";
    	} 

        // 计价方式 非空 数字 0 或者 1
        if(!is_integer($row[4]) || ($row[4] != 0 && $row[4] != 1))
        {
            $message.="第{$row_num} 行, 第 {$col[4]} 列;";  
        }

        // 5 规格，无要求
        
        // 6 商品单位 非空
    	if(empty($row[6])|| strlen($row[6])>20)
    	{
    		$message.="第{$row_num} 行, 第 {$col[6]} 列;";
    	} 


        // 7 进货价 非空 浮点
    	if(!is_float($row[7]) && !is_integer($row[7]))
    	{
    		$message.="第{$row_num} 行, 第 {$col[7]} 列;";
    	} 

        // 8 零售价 浮点 非空
    	if(!is_float($row[8]) && !is_integer($row[8]))
    	{
    		$message.="第{$row_num} 行, 第 {$col[8]} 列;";
    	} 

        // 9 初始库存 非空 整数
    	if(!is_integer($row[9]))
    	{
    		$message.="第{$row_num} 行, 第 {$col[9]} 列;";
    	} 

        // 10 库存预警 非空 整数
    	if(!is_integer($row[10]))
    	{
    		$message.="第{$row_num} 行, 第 {$col[10]} 列;";
    	} 


        // 11 货架号 无要求
        
        // 12 过期日期  不能查过当前日期
        if(!empty($row[12]))
        {
	        $obj = json_decode(json_encode($row[12], true));
	        if(!empty($obj)&&!is_null($obj))
	        {
	            $date = strval($obj->date);
	            // 条码不能为中文
	            if(strtotime($date)< time())
	            {
	                $message.="第{$row_num} 行, 第 {$col[12]} 列 过期;";

	            }   
	        }	
        } else {
			// $message.="第{$row_num} 行, 第 {$col[12]} 列 ;";        	
        }
        

        // 13 是否上架 非空 整数  0 或者 1
        if(!is_integer($row[13])||
          (intval($row[13]) != 0 && intval($row[13]) != 1))
        {
            $message.="第{$row_num} 行, 第 {$col[13]} 列;"; 
        }

        // 14 是否快捷 非空 整数 0 或者 1
        if(!is_integer($row[14])||
          (intval($row[14])!=0 && intval($row[14])!=1))
        {
            $message.="第{$row_num} 行, 第 {$col[14]} 列;";
        }

     	
    	// 过期日期不能超过当前时间
    	// if(preg_match("/^[0-9]+$/", $row[2]))
    	// {
    	// 	$message.="第{$row_num} 行, 第 {$col[2]} 列;";
    	// }
    

    	

		// if($row_num ==5)
		// {
  //           exit(gettype($row[12]));
		// 	exit(json_encode(is_integer($row[4])).'###'. json_encode(intval($row[4])).'###'.json_encode($row[4]==0).'###'.json_encode($row[4]==0));
		// 	exit(json_encode($row));
		// }  


    	if($message != "")
    	{
    		return $message;
    	} else {
    		return true;

    	}

    	return true;


    	
    }

    public function downloadExcel(Request $req)
    {
		// $file = File::get("../resources/logs/$id");
		$headers = array(
		   'Content-Type: application/octet-stream',
		);
		//exit(json_encode(file_exists(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/public/exceltemplate/import.xlsx")));;
		#return Response::download($file, $id. '.' .$type, $headers); 
		return response()->download(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/public/exceltemplate/import.xlsx", 'import.xlsx', $headers);
    }

}
