<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Salesman\Salesman;
use DataStatis;
use DB;
use App\Models\Videos\UserAddress;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiAdminController extends Controller
{
  const ONCE = 1;
  const EVERY_WEEK = 2;
  const EVERY_MONTH = 3;
  const EVERY_DAY = 4;

   public function __construct()
   {
       //
   }


    public function index(Request $request){
        //dd(DataStatis::set());
        $data=array_values(array_flip($request->input()));
       try{
           $where['is_del']=0;
           $result=Salesman::where($where);
           $name=$data[0];
          if($name){
               $result=$result->where(function($query)use($name){
                   $query->orwhere('salens_name','like','%'.$name.'%')
                       ->orwhere('mobile','like','%'.$name.'%');
               });
           }
           $result=$result->select('salens_name','mobile','id','add_time')->get();
       }catch(\Exception $e){
           $result=$e->getMessage();
       }
      return $result;
    }
    /**
     * 订货店铺数据接口
    */
    public function StoreRegion(Request $request){
        $res=[];
      try{
          $result=DB::connection('mysql_DH')->table('dsc_users as u')
              ->leftjoin('dsc_user_address as ua','u.user_id','=','ua.user_id')
              ->leftjoin('dsc_region as r','u.province_id','=','r.region_id')
              ->leftjoin('dsc_region as r1','u.city_id','=','r1.region_id')
              ->leftjoin('dsc_region as r2','u.district_id','=','r2.region_id')
              ->where('u.msn','<>','')->where('istrueshop','=',1)
              ->select('ua.lat','ua.lng','r.region_name as province','r1.region_name as city','r2.region_name as district','u.user_id','u.province_id','u.city_id','u.district_id','u.nick_name','ua.address')->get();
          if(!empty($result)){
              foreach($result as $k=>$v){
                  $result[$k]->is_direct=0;
              }
             $res['error']=1000;
             $res['data']=$result;
         }else{
             $res['error']=1004;
             $res['data']='null';
         }
      }catch(\Exception $e){
          $res['error']=1005;
          $res['data']=$e->getMessage();
      }
        return response()->json($res);
    }
    /**
     * 是否开发地区数据接口
     */
    public function RegionMessage(Request $request)
    {
        $message = [];
        $types=$request->get('type');
        if(empty($types)){
            $types='area';
        }
        $result = DB::table('crm_tv_region as tr');
        if($types=='province'){
            $result=$result->leftjoin('crm_region as r', 'tr.province', '=', 'r.region_id')
                ->select('tr.type','tr.id','r.region_name as province ');
        }
        if($types=='city'){
            $result=$result
                ->leftjoin('crm_region as r', 'tr.province', '=', 'r.region_id')
                ->leftjoin('crm_region as r1', 'tr.city', '=', 'r1.region_id')
            ->select('tr.type','tr.id','r.region_name as province ','r1.region_name as city');
        }
        if($types=='area'){
            $result=$result
                ->leftjoin('crm_region as r', 'tr.province', '=', 'r.region_id')
                ->leftjoin('crm_region as r1', 'tr.city', '=', 'r1.region_id')
                ->leftjoin('crm_region as r2', 'tr.area', '=', 'r2.region_id')
                ->select('tr.type','tr.id','r.region_name as province ','r1.region_name as city','r2.region_name as area');
        }
         $result = $result->get();
            if(!empty($result)){
                foreach ($result as $k => $v) {
                    if ($v->type == 0) {
                        $result[$k]->type_name = '已开发';
                    } elseif ($v->type == 1) {
                        $result[$k]->type_name = '待开发......';
                    } else {
                        $result[$k]->type_name = '开发中......';
                    }
                }
                $message['error'] = 1000;
                $message['data'] = $result;
            }else{
                $message['error'] = 1004;
                $message['data'] = '';
            }
            $message['recordsTotal'] = DB::table('crm_tv_region')->count();
            return response()->json($message);
    }

    public function getXyCoordinate(){
        $da='';
        $result=DB::connection('mysql_DH')->table('dsc_users as u')
            ->leftjoin('dsc_user_address as ua','u.user_id','=','ua.user_id')
            ->where('ua.lat','=','0')
            ->where('ua.lng','=','0')->where('istrueshop','=',1)->select('ua.address_id','ua.address')->paginate(1000);
        try{
            $res=UserAddress::getAddress($result);
            if($res==false){
             $da='error401';
            }else{
                $da='success';
            }
        }catch(\Exception $e){
           echo $e->getMessage();
        }
        return $da;
    }

    /**
     * 业务员登录接口
     * @return [type] [description]
     */
    public function agentLogin(Request $req)
    {

      $username = $req->get('name');
      $password = $req->get('password');

      if(empty($username))
      {
        return response()->json(['code'=>0,'error_code'=>101,'message'=>'mobilephone can not be empty']);
      }

      if(empty($password))
      {
        return response()->json(['code'=>0,'error_code'=>102,'message'=>'password can not be empty']);
      }

      // 查询用户信息，如果存在，返回用户信息
      $where['mobile'] = $req->get('name');
      $where['password'] = $req->get('password');
      $where['is_del'] =0;

      $result = DB::table('crm_salesman as sl')->where($where)->first();
      if(empty($result->password))
      {
        return response()->json(['code'=>0,'error_code'=>103,'message'=>'用户名密码组合不正确']);
      } else {
        return response()->json(['code'=>1,'error_code'=>0,'message'=>'success','data'=>$result]);
      }
      return $this->JsonFail();
    }

    /**
     * 跑店列表
     * 根据日期 和业务员 返回 需要访问店铺列表
     * @return [type] [description]
     */
    public function storeToVisit(request $req)
    {

      $agent_id = $req->get('id');
      $data_stamp = $req->get('date');

      if(empty($agent_id))
      {
        return response()->json(['code'=>0,'error_code'=>100,'message'=>'id can not be empty']);
      }

      if(empty($data_stamp))
      {
        return response()->json(['code'=>0,'error_code'=>101,'message'=>'date can not be empty']);
      }

      // 查找所有分配到改业务员的店铺信息，根据循环信息筛选
      $where['sd.agent_id'] = $agent_id;
      $where['sd.is_deleted'] = 0;
      $result = DB::table('crm_schedule as sd')->leftjoin('crm_store as st', 'st.id', '=', 'sd.store_id')
                                              ->select('st.*','sd.agent_id','sd.start_time','sd.cycle','sd.is_deleted')
                                              ->where($where)
                                              ->get();
      if(empty($result[0]->id))
      {
        // 没有记录
        exit(json_encode(['code'=>0, 'error_code'=>102,'message'=>'no records found','data'=>[]]));
      } else {
        $list = $this->getTaskList($result, $data_stamp, $agent_id);
      }

      return exit(json_encode(['code'=>0, 'error_code'=>103,'message'=>"oops, something went wrong", 'data'=>[]]));
    }


    /**
     * 店铺打卡
     * @return [type] [description]
     */
    public function mark(request $req)
    {
      $agent_id = $req->get('id');
      $store_id = $req->get('store_id');
      $remark = $req->get('remark');
      $location = $req->get('addressLocation');

      if(empty($agent_id))
      {
        return response()->json(['code'=>0,'error_code'=>100,'message'=>'id can not be empty']);
      }

      if(empty($store_id))
      {
        return response()->json(['code'=>0, 'error_code'=>101, 'message'=>'store_id can not be empty']);
      }

      if(empty($location))
      {
        return response()->json(['code'=>0, 'error_code'=>102, 'message'=>'经纬度数据不能为空']);
      }

      $lat = 0;
      $long = 0;
      if(is_array($location))
      {
        $lat = $location[0];
        $long = $location[1];
      }

      // 查询是否有打卡任务
      $where['sales_id'] = $agent_id;
      $where['id'] = $store_id;
      $result = DB::table('crm_store as tl')->where($where)->first();
      if(empty($result->id))
      {
        // 没有打卡任务
        return response()->json(['code'=>0,'error_code'=>102,'message'=>"task not exist",'data'=>[]]);
      } else {
        // 存在打卡任务
        // 检查距离
        $dis = $this->distance($lat, $long, $result['lat'], $result['long']);
        if(intval($dis)===false)
        {
            return response()->json(['code'=>0,'error_code'=>103,'message'=>"距离计算失败!",'data'=>[]]);
        }

        if(intval($dis)>5000)
        {
            return response()->json(['code'=>0,'error_code'=>103,'message'=>"{$result['lat']}, {$result['long']} 计算距离为 {$dis}, 打卡失败",'data'=>[]]);
        }

        return response()->json(['code'=>1,'error_code'=>0,'message'=>"success,距离 {$dis}",'data'=>[]]);

      }
      
      // 验证
      return response()->json(['code'=>0,'error_code'=>1000,'message'=>" not implement yet",'data'=>[]]);
    }

    /**
     * 保存店铺接口
     * @param  request $req [description]
     * @return [type]       [description]
     */
    public function saveStore(request $req)
    {

      $data = $this->saveStoreValidate($req);

      if(!is_array($data) || sizeof($data) < 1)
      {
        exit(json_encode(['code'=>0, 'error_code'=>150, 'message'=>"can not find enough information form store ", 'data'=>[]]));
      }

      // 事务
      DB::beginTransaction();
      try {
            $arr = $this->createStore($data['store_name'], $data['agent_id'], $data['store_ramk'], $data['type'], $data['lat'], $data['long']);
            $store_id = $arr[0];
            $store_sn = $arr[1];
            $this->createStoreOwner($store_id, "whoami", $data['tmp1'], $data['tmp8'], $data['tmp4']);
            $this->createStoreMessage($store_id, $data);
            $this->createStoreCardMessage($store_id);
            $this->createStoreExamin($store_sn); 
            DB::commit();
            exit(json_encode(['code'=>1,'error_code'=>0, 'message'=>'success', 'data'=>['store_id'=>$store_id,'store_sn'=>$store_sn]]));
      } catch (Exception $e) {
            DB::rollBack();
            exit(json_encode(['code'=>0,'error_code'=>1001, 'message'=>$e->getMessage, 'data'=>[]]));
      }

      exit(json_encode(['code'=>0,'error_code'=>1000, 'message'=>'not implement yet', 'data'=>[]]));
      // 其它接口
    }


    /**
     * 获取店铺详情
     * @param  request $req [description]
     * @return [type]       [description]
     */
    public function getStoreInfo(request $req)
    {
        // 获取店铺id
        $store_id = $req->get('id');

        if(empty($store_id))
        {
            return response()->json(['code'=>0,'error_code'=>100,'message'=>"店铺id 不能为空",'data'=>[]]);
        }

        $where['st.id'] = $store_id;
        $result = DB::table('crm_store as st')->leftjoin('crm_store_message as sm', 'st.id', '=', 'sm.store_id')
                                              ->leftjoin('crm_store_card_message as sc', 'st.id', '=', 'sc.store_id')
                                              ->leftjoin('crm_store_examine as se', 'st.store_sn', '=', 'se.store_sn')
                                              ->leftjoin('crm_msg_shopowner as so', 'st.id', '=', 'so.store_id')
                                              ->leftjoin('crm_salesman as sa', 'st.sales_id', '=', 'sa.id')

                                              ->select('st.*',"sm.address", "sm.store_thumb","sm.store_img","sm.business_license", "sm.personnel_num", "sm.personnel_acreage", "sm.around_store_num", "sm.daily_turnover", "sm.daily_turnover", "sm.business_duration", "sm.is_pos", "sm.coordination_type", "sm.shop_location", "sm.radiant_region", "sm.store_use_years", "sm.is_chain","sm.replenishment_method", "sm.is_after_sale", "sm.shop_cleaning", "sm.is_app", "sm.store_ramk", "sm.shelf_quantity","sm.ice_num", "sm.cabinet_num", "sm.business_description", "sm.business_classification", "sm.pile_head","sm.is_express","sm.store_expectations","sm.development_standard","sm.is_dinghuo","sm.house_attribute","sm.pile_head_remarks","sm.radiation_region_check_result","sm.store_code","so.shopowner_name","so.shopowner_sex","so.shopowner_age","so.shopowner_mobile","so.shopowner_affinity","se.is_active",  "sa.salens_name as agent_name", "sa.mobile as agent_mobile","sm.store_code")
                                              ->where($where)
                                              ->get();

        foreach ($result as $key => $value) {
            $result[$key]->store_img                     =  json_decode($value->store_img); 
            $result[$key]->store_thumb                   = json_decode($value->store_thumb); 
            $result[$key]->business_license              =json_decode($value->business_license); 
            $result[$key]->business_classification       = json_decode($value->business_classification);
            $result[$key]->is_express                    = json_decode($value->is_express);
            $result[$key]->radiation_region_check_result = json_decode($value->radiation_region_check_result);


        }

        exit(json_encode(['code'=>1,'error_code'=>0, 'message'=>'success', 'data'=>['list'=>$result]]));
    }

    public function getStoreList(request $req)
    {
        $agent_id = $req->get('id');

        if(empty($agent_id))
        {
            return response()->json(['code'=>0,'error_code'=>100,'message'=>"业务员id 不能为空",'data'=>[]]);
        }

        $where['st.sales_id'] = $agent_id;
        $result = DB::table('crm_store as st')->leftjoin('crm_store_message as sm', 'st.id', '=', 'sm.store_id')
                                              ->leftjoin('crm_store_card_message as sc', 'st.id', '=', 'sc.store_id')
                                              ->leftjoin('crm_store_examine as se', 'st.store_sn', '=', 'se.store_sn')
                                              ->leftjoin('crm_salesman as sa', 'st.sales_id', '=', 'sa.id')

                                              ->select('st.*','sm.store_thumb','sm.store_img','sm.province','sm.city','sm.region','sm.address',"sm.store_code",'sm.shop_location',"se.is_active", "sa.salens_name as agent_name", "sa.mobile as agent_mobile")
                                              ->orderBy('st.created_at', 'desc')
                                              ->where($where)
                                              ->get();
        foreach ($result as $key => $value) {
            $result[$key]->store_thumb = json_decode($value->store_thumb); 
            $result[$key]->store_img   =  json_decode($value->store_img); 
        }

        exit(json_encode(['code'=>1,'error_code'=>0, 'message'=>'success', 'data'=>['list'=>$result]]));

    }

    /**
     * 微信后台图片上传接口
     * @param  request $req [description]
     * @return [type]       [description]
     */
    public function wximageupload(request $req)
    {

      $tag = "shop";
      if(empty($_FILES['shop']))
      {
        return response()->json(['code'=>0, 'error_code'=>100, 'message'=>"shop image information not found", 'data'=>[]]);

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
      $uploadfile = './img/' .$new_file;
      // 保存文件名
      if (move_uploaded_file($_FILES[$tag]['tmp_name'], $uploadfile)) {
          return response()->json(['code'=>1, 'error_code'=>0, 'message'=>'success', 'data'=>['image_path'=>"http://{$_SERVER['HTTP_HOST']}/img/{$new_file}"]]);
      } else {
          return response()->json(['code'=>0, 'error_code'=>101, 'message'=>'upload failed', 'data'=>[]]);
      }

      return response()->json(['code'=>1, 'error_code'=>102, 'message'=>"oops, something went wrong", 'data'=>[]]);

    }


    /**
     * 获得工作列表
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    private function getTaskList($result, $date_stamp='', $agent_id)
    {
      if(empty($date_stamp))
        $data_stamp = time();

      $today_drawn = date('Y-m-d 0:0:0', $date_stamp);
      $today_drawn_timestamp = strtotime($today_drawn);
      $today_midnight = date('Y-m-d 23:59:59', $date_stamp);
      $today_midnight_timestamp = strtotime($today_midnight);
      $day = intval(date('d', $date_stamp));
      $week_day = date("D", $date_stamp);

      $data_list = [];
      foreach ($result as $key => $value) {
        // 检查循环类型
        switch ($value->cycle) {
          case self::ONCE:
            // 单次
            if($today_drawn_timestamp < $value->start_time  && $value->start_time < $today_midnight_timestamp)
            {
              $data_list[] = $value;
            }else {
            }
            break;
          case self::EVERY_WEEK:
            // 每周
            $comp =  date("D",$value->start_time);
            if(trim($comp) == trim($week_day)  && $value->start_time < $today_midnight_timestamp)
            {
              $data_list[] = $value;

            }
            break;
          case self::EVERY_MONTH:
            // 每月
            $comp = intval(date('d', $value->start_time));
            if($comp == $day  && $value->start_time < $today_midnight_timestamp)
            {
              $data_list[] = $value;

            }
            break;
          case self::EVERY_DAY:
            // 每天
            if($value->start_time < $today_midnight_timestamp)
            {
              $data_list[] = $value;
            }
            break;
          default:
            # code...
            break;
        }
        
      }

      $this->existOrInsert($data_list, $today_drawn_timestamp,$agent_id);

      exit(json_encode(['code'=>1,'error_code'=>0,'message'=>"success",'data'=>$data_list]));

    }

    /**
     * 检查数据库的任务清单，如果存在改业务员的，不做任何处理，否则插入清单
     * @param  [type] $data_list  [description]
     * @param  [type] $created_at [description]
     * @return [type]             [description]
     */
    private function existOrInsert($data_list, $created_at, $agent_id)
    {
        // 检查是否存在任务列表，不存在则插入任务列表
        $where['created_at'] = $created_at;
        $where['agent_id'] = $agent_id;
        $count = DB::table('crm_task_list as tl')->where($where)->count();
        if(intval($count)!==false&&$count>0)
        {
          // 如果存在，不做任务处理
        }else {
          // 插入数据
          if(sizeof($data_list) < 1)
            return;

          $insert_data=[];
          foreach ($data_list as $key => $value) {
            $tmp = [];
            $tmp['agent_id'] = $value->agent_id;
            $tmp['store_id'] = $value->id;
            $tmp['store_name'] = strval($value->store_name);
            $tmp['created_at'] = $created_at;

            DB::table('crm_task_list')->insert($tmp);
          }
        }
    }

    /**
     * 验证表单数据
     * @param  request $req [description]
     * @return [type]       [description]
     */
    private function saveStoreValidate(request $req)
    {
      $store_name              = $req->get('name');  // 店铺名称 *
      $type                    = $req->get('type'); // 店铺类型 *
      $shop_cleaning           = $req->get('shopPut'); // 摆放，清洁程度 *
      $around_store_num        = $req->get('shopNum'); // 周边门店数量 *
      $daily_turnover          = $req->get('dayTurnover');  // 日营业额度 *
      $personnel_acreage       = $req->get('businessArea'); // 营业面积 *
      $shelf_quantity          = $req->get('shelfQuantity'); // 货架数量 *
      $is_pos                  = $req->get('posMachine'); // 是否使用pos级  *
      $pile_head               = $req->get('pileHead');  // 推头情况 *
      $replenishment_method    = $req->get('replenishmentMode');
      $tmp1                    = $req->get('shopkeeperAge'); // 店主年龄 *   ?
      $tmp2                    = $req->get('shopLife'); // 店铺年限 * ?
      $personnel_num           = $req->get('shopAssistant'); // 营业员数数量 *
      $is_after_sale           = $req->get('isIncrement'); // 是否接受增值服务 *
      $is_dinghuo              = $req->get('attitudeToPlatform'); // 订货平台态度 *
      $business_duration       = $req->get('businessHours'); // 营业时间 *  ?
      $tmp3                    = $req->get('houseAttribute'); // 房屋属性 * ?
      $tmp4                    = $req->get('shopkeeperAffinity'); // 店主亲合力 *
      $address                 = $req->get('addressInfo'); // 地址 *
      $radiant_region          = $req->get('radiationRegion'); // 辐射区域 *
      $tmp5                    = $req->get('other');  //其他经营品描述  ?
      $tmp6                    = $req->get('pileHeadRemarks'); // 堆头情况备注 * ?
      $store_ramk              = $req->get('remarks'); // 店主备注 经营描述？ ？？？
      $tmp7                    = $req->get('radiationRegionCheckResult'); 
      $business_classification = $req->get('categoryCheckResult'); //经营分类
      $is_express              = $req->get('isExpressResult');// 是否代收快递 * 
      $tmp8                    = $req->get('phone');  // 门店电话  * ?
      $ice_num                 = $req->get('refrigeratorNum'); // 冰箱数量 *  
      $tmp9                    = $req->get('displayCaseNum');  // 陈列柜？？？  *
      $agent_id                = $req->get('agent_id'); // 业务员id
      $img                     = $req->get('imgArray'); // 照片
      $store_head              = $req->get('imgheadArray');
      $lic_pic                 = $req->get('imgbusinessLicense');
      $shop_location           = $req->get('addressText');
      $address_info            = $req->get('addressText');
      $latlon                  = $req->get('addressLocation');
      $lat                     = $latlon[0];
      $long                    = $latlon[1];
      
     $province                = $req->get('province');
      $city                    = $req->get('city');
      $region                  = $req->get('region');

      if(empty($province))
      {
        $province = "";
      }

      if(empty($city))
      {
        $city = "";
      }

      if(empty($region))
      {
        $region = "";
      }

      // 
      if(empty($store_name))
      {
        exit(json_encode(['code'=>0, 'error_code'=>100, 'message'=>'店铺名称name不能为空','data'=>[]]));
      }

      if(empty($type))
      {
        exit(json_encode(['code'=>0, 'error_code'=>101, 'message'=>'店铺类型type不能为空','data'=>[]]));
      }

      if(empty($shop_cleaning))
      {
        exit(json_encode(['code'=>0, 'error_code'=>102, 'message'=>'摆放清洁shopPut不能为空','data'=>[]]));
      }

      if(empty($around_store_num))
      {
        exit(json_encode(['code'=>0, 'error_code'=>103, 'message'=>'周边门店数量shopNum不能为空','data'=>[]]));
      }

      if(empty($daily_turnover))
      {
        exit(json_encode(['code'=>0, 'error_code'=>104, 'message'=>'日营业额度dayTurnover不能为空','data'=>[]]));
      }

      if(empty($personnel_acreage))
      {
        exit(json_encode(['code'=>0, 'error_code'=>105, 'message'=>'店铺面积businessArea不能为空','data'=>[]]));
      }

      if(empty($shelf_quantity))
      {
        exit(json_encode(['code'=>0, 'error_code'=>106, 'message'=>'货架数量shelfQuantity不能为空','data'=>[]]));
      }

      if(empty($is_pos))
      {
        exit(json_encode(['code'=>0, 'error_code'=>107, 'message'=>'是否使用POS字段posMachine不能为空','data'=>[]]));
      }

      if(empty($pile_head))
      {
        // exit(json_encode(['code'=>0, 'error_code'=>108, 'message'=>'堆头pileHead不能为空','data'=>[]]));
      }

      if(empty($replenishment_method))
      {
        exit(json_encode(['code'=>0, 'error_code'=>109, 'message'=>'补货方式replenishmentMode不能为空','data'=>[]]));
      }

      if(empty($tmp1))
      {
        exit(json_encode(['code'=>0, 'error_code'=>110, 'message'=>'店主年龄shopkeeperAge不能为空','data'=>[]]));
      }

      if(empty($tmp2))
      {
        exit(json_encode(['code'=>0, 'error_code'=>111, 'message'=>'店主年限shopLife不能为空','data'=>[]]));
      }

      if(empty($personnel_num))
      {
        exit(json_encode(['code'=>0, 'error_code'=>112, 'message'=>'营业员数量shopAssistant不能为空','data'=>[]]));
      }

      if(empty($is_after_sale))
      {
        exit(json_encode(['code'=>0, 'error_code'=>113, 'message'=>'是否接受增值服务isIncrement不能为空','data'=>[]]));
      }

      if(empty($is_dinghuo))
      {
        exit(json_encode(['code'=>0, 'error_code'=>114, 'message'=>'订货平台态度attitudeToPlatform不能为空','data'=>[]]));
      }

      if(empty($business_duration))
      {
        exit(json_encode(['code'=>0, 'error_code'=>115, 'message'=>'营业时间businessHours不能为空','data'=>[]]));
      }

      if(empty($tmp3))
      {
        exit(json_encode(['code'=>0, 'error_code'=>116, 'message'=>'房屋属性houseAttribute不能为空','data'=>[]]));
      }

      if(empty($tmp4))
      {
        exit(json_encode(['code'=>0, 'error_code'=>117, 'message'=>'店主亲和力shopkeeperAffinity不能为空','data'=>[]]));
      }

      if(empty($address))
      {
        exit(json_encode(['code'=>0, 'error_code'=>118, 'message'=>'当前地址addressInfo不能为空','data'=>[]]));
      }

      if(empty($radiant_region))
      {
        exit(json_encode(['code'=>0, 'error_code'=>119, 'message'=>'辐射区域radiationRegion不能为空','data'=>[]]));
      }

      if(empty($tmp5))
      {
        exit(json_encode(['code'=>0, 'error_code'=>120, 'message'=>'其它经营描述other不能为空','data'=>[]]));

      }

      if(empty($tmp6))
      {
        //exit(json_encode(['code'=>0, 'error_code'=>121, 'message'=>'堆头情况备注pileHeadRemarks不能为空','data'=>[]]));
      }

      if(empty($store_ramk))
      {
        //exit(json_encode(['code'=>0, 'error_code'=>122, 'message'=>'店主情况备注remarks不能为空','data'=>[]]));
      }

      if(empty($tmp7))
      {
        exit(json_encode(['code'=>0, 'error_code'=>123, 'message'=>'辐射描述 radiationRegionCheckResult 不能为空','data'=>[]]));
        
      }

      if(empty($business_classification))
      {
        exit(json_encode(['code'=>0, 'error_code'=>124, 'message'=>'店铺经营分类categoryCheckResult不能为空','data'=>[]]));
      }

      if(empty($is_express))
      {
        exit(json_encode(['code'=>0, 'error_code'=>125, 'message'=>'是否代收包裹isExpressResult不能为空','data'=>[]]));
      }

      if(empty($tmp8))
      {
        exit(json_encode(['code'=>0, 'error_code'=>126, 'message'=>'店主电话 phone 不能为空','data'=>[]]));
      }

      if(empty($ice_num))
      {
        exit(json_encode(['code'=>0, 'error_code'=>127, 'message'=>'冰箱数量refrigeratorNum不能为空','data'=>[]]));
      }

      if(empty($tmp9))
      {
        
        exit(json_encode(['code'=>0, 'error_code'=>1271, 'message'=>'陈列柜 displayCaseNum 不能为空','data'=>[]]));
      }

      if(empty($agent_id))
      {
        exit(json_encode(['code'=>0, 'error_code'=>128, 'message'=>'业务员id agent_id 不能为空!','data'=>[]]));
      }

      if(empty($img))
      {
        exit(json_encode(['code'=>0, 'error_code'=>129, 'message'=>'图片 imgArray 不能为空!','data'=>[]]));
      }

      if(empty($store_head))
      {
        exit(json_encode(['code'=>0, 'error_code'=>130, 'message'=>'门头照 imgheadArray 不能为空!','data'=>[]]));
      }

      if(empty($lic_pic))
      {
        //exit(json_encode(['code'=>0, 'error_code'=>131, 'message'=>'营业执照 imgbusinessLicense 不能为空!','data'=>[]]));
      }

      if(intval($lat)===false || intval($lat) <=0)
      {
        exit(json_encode(['code'=>0, 'error_code'=>132, 'message'=>'经度数据不合法!','data'=>[]]));
      }

      if(intval($long)===false || intval($long) <=0)
      {
        exit(json_encode(['code'=>0, 'error_code'=>133, 'message'=>'纬度数据不合法!','data'=>[]]));
      }

      if(intval($agent_id)===false||intval($agent_id)<=0)
      {
        exit(json_encode(['code'=>0, 'error_code'=>134, 'message'=>'业务员id agent_id 类型不合法!','data'=>[]]));
      }
      

      $tmp = [];
      $tmp['store_name']              = $store_name;
      $tmp['type']                    = $type;
      $tmp['shop_cleaning']           = $shop_cleaning;
      $tmp['around_store_num']        = $around_store_num;
      $tmp['daily_turnover']          = $daily_turnover;
      $tmp['personnel_acreage']       = $personnel_acreage;
      $tmp['shelf_quantity']          = $shelf_quantity;
      $tmp['is_pos']                  = $is_pos;
      $tmp['pile_head']               = $pile_head;
      $tmp['replenishment_method']    = $replenishment_method;
      $tmp['tmp1']                    = $tmp1;
      $tmp['tmp2']                    = $tmp2;
      $tmp['personnel_num']           = $personnel_num;
      $tmp['is_after_sale']           = $is_after_sale;
      $tmp['is_dinghuo']              = $is_dinghuo;
      $tmp['business_duration']       = $business_duration;
      $tmp['tmp3']                    = $tmp3;
      $tmp['tmp4']                    = $tmp4;
      $tmp['address']                 = $address;
      $tmp['radiant_region']          = $radiant_region;
      $tmp['tmp5']                    = $tmp5;
      $tmp['tmp6']                    = $tmp6;
      $tmp['store_ramk']              = $store_ramk;
      $tmp['tmp8']                    = $tmp8;
      $tmp['ice_num']                 = $ice_num;
      $tmp['tmp9']                    = $tmp9;
    
      $tmp['agent_id']                = $agent_id;
      $tmp['lat']                     = $lat;
      $tmp['long']                    = $long;
      $tmp['shop_location']           =$shop_location;

      $tmp['img']                     = json_encode($img);
      $tmp['head_img']                = json_encode($store_head);
      $tmp['business_license']        = json_encode($lic_pic);
      $tmp['is_express']              = json_encode($is_express);
      $tmp['business_classification'] = json_encode($business_classification);
      $tmp['tmp7']                    = json_encode($tmp7);

      $tmp['province'] = $province;
      $tmp['city'] = $city;
      $tmp['region'] = $region;




      return $tmp;

    }

    /**
     * 创建store 表，如果成功，返回id
     * @param  [type] $store_name [description]
     * @param  [type] $agent_id   [description]
     * @param  [type] $store_ramk [description]
     * @param  [type] $type       [description]
     * @return [type]             [description]
     */
    private function createStore($store_name, $agent_id, $store_ramk, $type, $lat=0, $long=0)
    {
      
      $dat=Carbon::now()->toDateTimeString();
      $store_sn = time().rand(10000,9999);
      
      $store['store_sn']       = $store_sn;
      $store['store_old_name'] =$store_name;
      $store['store_name']     =$store_name;
      $store['sales_id']       =$agent_id;
      $store['ramk']           =$store_ramk;
      $store['is_active']      = 0;
      $store['store_type']     = $type;
      $store['created_at']     = $dat;
      $store['updated_at']     =$dat;
      $store['lat']            =$lat;
      $store['long']           =$long;


      $store_result=DB::table('crm_store')->insertGetId($store);

      if($store_result=== false)
      {
        exit(json_encode(['code'=>0, 'error_code'=>301, 'message'=>'创建store 表是吧','data'=>[]]));
      }
      return [$store_result, $store_sn];

    }

    /**
     * 创建shopper owern 
     * @param  [type] $id                 [description]
     * @param  [type] $owner_name         [description]
     * @param  [type] $age                [description]
     * @param  [type] $phone              [description]
     * @param  [type] $shopowner_affinity [description]
     * @return [type]                     [description]
     */
    private function createStoreOwner($id, $owner_name, $age, $phone, $shopowner_affinity)
    {
        $dat=Carbon::now()->toDateTimeString();

        $store_shopowner['store_id']           =$id;
        $store_shopowner['shopowner_name']     =$owner_name;
        $store_shopowner['shopowner_sex']      = 0;
        $store_shopowner['shopowner_age']      =$age;
        $store_shopowner['shopowner_mobile']   =$phone;
        $store_shopowner['tell_phone']         = $phone;
        $store_shopowner['wechat']             ='wechat';
        $store_shopowner['shopowner_affinity'] = $shopowner_affinity;
        $store_shopowner['created_at']         =$dat;
        $store_shopowner['updated_at']         =$dat;
        $store_shopowner=DB::table('crm_msg_shopowner')->insert($store_shopowner);

        if($store_shopowner=== false)
        {
          throw new Exception("shopper owenr 创建失败", 1);
        }
      
      return $store_shopowner;

    }

    /**
     * 创建 store message
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function createStoreMessage($id, $data)
    {
        $dat=Carbon::now()->toDateTimeString();
      
      $store_message['store_id']                =$id;
      
       
       $store_message['address']                       = $data['address'];
       $store_message['personnel_num']                 =$data['personnel_num'];
       $store_message['personnel_acreage']             =$data['personnel_acreage'];
       $store_message['around_store_num']              =$data['around_store_num'];
       $store_message['daily_turnover']                =$data['daily_turnover'];
       $store_message['business_duration']             =$data['business_duration'];
       $store_message['shop_location']                 = $data['shop_location'];
       $store_message['radiant_region']                =$data['radiant_region'];
       $store_message['store_use_years']               = $data['tmp2'];
       $store_message['replenishment_method']          = $data['replenishment_method'];
       $store_message['is_after_sale']                 =$data['is_after_sale'];
       $store_message['shop_cleaning']                 =$data['shop_cleaning'];
       $store_message['is_pos']                        =$data['is_pos'];
       $store_message['store_thumb']                   =$data['img'];
       $store_message['store_img']                     =$data['head_img'];
       $store_message['business_license']              =$data['business_license'];
       $store_message['created_at']                    =$dat;
       $store_message['updated_at']                    =$dat;
       $store_message['is_express']                    = $data['is_express'];
       $store_message['pile_head']                     =$data['pile_head'];
       $store_message['shelf_quantity']                =$data['shelf_quantity'];
       $store_message['ice_num']                       =$data['ice_num'];
       $store_message['cabinet_num']                   =$data['tmp9'];
       $store_message['business_description']          = $data['tmp5'];
       $store_message['business_classification']       = $data['business_classification'];
       $store_message['house_attribute']               = $data['tmp3'];
       $store_message['pile_head_remarks']             = $data['tmp6'];
       $store_message['radiation_region_check_result'] = $data['tmp7'];
       $store_message['is_dinghuo']                    = $data['is_dinghuo'];
       $store_message['province']                      = $data['province'];
       $store_message['city']                          = $data['city'];
       $store_message['region']                        = $data['region'];




      $store_message=DB::table('crm_store_message')->insert($store_message);

        if($store_message=== false)
        {
          throw new Exception("shopper message 创建失败", 1);
        }
      
      return $store_message;
    }
    
    private function createStoreExamin($store_sn, $admin_id=1)
    {
      $datas['store_sn']=$store_sn;
      $datas['admin_id']=$admin_id;
      $datas['is_active']=0;
      $datas['ramk']="";


      $dat=Carbon::now()->toDateTimeString();
      $examine_id='EX'.mt_rand(1000,9999999);
      $result=[
      'examine_id'=>$examine_id,
      'store_sn'=>$store_sn,
      'examiner'=>1,
      'is_active'=>1,
      'ramk'=>"",
      'created_at'=>$dat,
      'updated_at'=>$dat
      ];
      $results=DB::table('crm_store_examine')->insert($result);
      if($results=== false)
      {
        throw new Exception("shopper card 创建失败", 1);
      }
    
    }

    private function createStoreCardMessage($store_id)
    {
        $data['store_id'] = $store_id;
        $data['created_at'] = Carbon::now()->toDateTimeString();
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $results=DB::table('crm_store_card_message')->insert($data);
        if($results=== false)
        {
            throw new Exception("shopper card message 创建失败", 1);
        }
    }

    /**
     * 计算经纬度距离（单位 毫米）
     * @param  [type]  $lat1  [description]
     * @param  [type]  $lng1  [description]
     * @param  [type]  $lat2  [description]
     * @param  [type]  $lng2  [description]
     * @param  boolean $miles [description]
     * @return [type]         [description]
     */
    private  function distance($lat1, $lng1, $lat2, $lng2)
    {
         $pi80 = M_PI / 180;
         $lat1 *= $pi80;
         $lng1 *= $pi80;
         $lat2 *= $pi80;
         $lng2 *= $pi80;
         $r = 6372797000; // mean radius of Earth in km
         $dlat = $lat2 - $lat1;
         $dlng = $lng2 - $lng1;
         $a = sin($dlat/2)*sin($dlat/2)+cos($lat1)*cos($lat2)*sin($dlng/2)*sin($dlng/2);
         $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
         $km = $r * $c;
         return $km;
    }

    private function returnArr($tmp)
    {
        if(is_array($tmp))
            return $tmp;


        // 是否是 json 字符串
        if(null == json_decode($tmp, true))
        {
            return [];
        }

        return [];

    }


}
