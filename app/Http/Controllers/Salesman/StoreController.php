<?php

namespace App\Http\Controllers\salesman;

use Illuminate\Http\Request;
use App\Events\permChangeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Salesman\Salesman;
use App\Tool\ImgToolClass;
use DB;
use Auth,Session,Event;
use Carbon\Carbon;

class StoreController extends Controller
{
    public function __construct()
    {
        //
    }
    public function index(Request $request,$cid=0)
    {
        if($request->ajax()){
            $cid=$request->get('cid',0);
            $start = $request->get('start');
            $length = $request->get('length');
            $order = $request->get('order');
            $columns = $request->get('columns');
            $search = $request->get('search');
            $k=$order[0]['column'];
            $name=$search['value'];
            $order_type=$order[0]['dir'];
            $orderby_name=$columns[$k]['data'];

            $search_value = $request->get('search_value');
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $name = $search_value;
            $search = $name;

            $result=DB::table('crm_store as s')
                ->leftjoin('crm_store_message as sm','s.id','=','sm.store_id')
                ->leftjoin('crm_msg_shopowner as msh','s.id','=','msh.store_id')
                ->leftjoin('crm_salesman as sa','sa.id','=','s.sales_id')
                ->where('s.is_del','=','0');
            if(!empty($search)){
                $result=$result->where(function($query)use($name){
                    $query->orwhere('s.store_name','LIKE','%'.trim($name).'%')
                        ->orwhere('s.store_sn','LIKE','%'.trim($name).'%')
                        ->orwhere('msh.shopowner_mobile','LIKE','%'.trim($name).'%')
                        ->orwhere('s.sales_id','LIKE','%'.trim($name).'%');
                });
            }

            if(!empty($start_date)&&!empty($end_date))
            {
              $result=$result->where(function($query)use($start_date, $end_date){
                      $query->whereBetween('s.created_at',array($start_date, $end_date));
                  });
            } elseif (!empty($start_date))
            {
              $result=$result->where(function($query)use($start_date){
                      $query->orwhere('s.created_at','>=',$start_date)
                          ;
                  });
            } elseif (!empty($end_date))
            {
              $result=$result->where(function($query)use($end_date){
                      $query->orwhere('s.created_at','=<',$end_date)
                          ;
                  });
            }else {

            }


            $message['recordsFiltered']=$result->count();
            $result=$result->skip($start)->take($length);
            // $result=$result->orderBy($orderby_name,$order_type)
            $result=$result->orderBy("s.created_at","desc")

                ->select('sa.salens_name','s.created_at','s.id','s.store_sn','s.store_name','s.store_old_name','s.sales_id','s.ramk','s.is_active','msh.shopowner_mobile','msh.shopowner_name','sm.province','sm.city','sm.region','sm.address')
                ->get();
            foreach($result as $k=>$v){
                $result[$k]->address=$v->province.$v->city.$v->region.$v->address;
                if($result[$k]->is_active==1){
                    $result[$k]->is_active='未审核';
                }elseif($result[$k]->is_active==2){
                    $result[$k]->is_active='审核通过';
                }elseif($result[$k]->is_active==3){
                    $result[$k]->is_active='审核不通过';
                }elseif($result[$k]->is_active==4){
                    $result[$k]->is_active='延迟处理';
                }else{
                    $result[$k]->is_active='未审核';
                }
            }
            $message['data']=$result;
            $message['draw'] = $request->get('draw');
            $message['recordsTotal']=DB::table('crm_store')->where('is_del','=','0')->count();
            return response()->json($message);
        }
        $message['cid']=(int)$cid;
        return view('salesman.store.index',$message);
    }

    public function create(Request $request){
      if($request->getMethod()=="POST")
      {
        // 调用 store
        return $this->store($request);
        exit;
      }
        $store_id='XX'.mt_rand(100,9999999);
        $sales_id=Salesman::where('is_del','=',0)->select('id','salens_name')->get();
        $rest=DB::table('crm_range')->select('id','name')->get();
        $message['store_id']=$store_id;
        $message['sales_id']=$sales_id;
        return view('salesman.store.create',compact('message','rest'));
    }
    public function store(Request $request){
        DB::beginTransaction();
        $dat=Carbon::now()->toDateTimeString();
        if(count($request->get('business_classification'))>0){
            $business_classification=$request->get('business_classification');
            // $business_classification=implode(',',$business_classification);
        }
        if(count($request->get('store_expectations'))>0){
            $store_expectations=$request->get('store_expectations');
            $store_expectations=implode(',',$store_expectations);
        }
        if(count($request->get('is_express'))>0){
            $is_express=$request->get('is_express');
            // $is_express=implode(',',$is_express);
        }
        if(count($request->get('development_standard'))>0){
            $development_standard=$request->get('development_standard');
            $development_standard=implode(',',$development_standard);
        }
        try{
            $loginTokenName = Auth::guard('admin')->getName();
            $operator=Session::get($loginTokenName);
            $store_data['licenseA']=$request->get('licenseA');
            $store_data['licenseB']=$request->get('licenseB');
            $store_data['licenseF']=$request->get('licenseF');
            $store_data['licenseH']=$request->get('licenseH');
            $store_data['licenseL']=$request->get('licenseL');
            $res=$this->imgsUrl($store_data);
            $store['store_sn']=$request->get('store_sn');
            $store['store_old_name']=$request->get('store_old_name');
            $store['store_name']=$request->get('store_name');
            $store['sales_id']=$request->get('sales_id');
            $store['ramk']=$request->get('store_ramk');
            $store['is_active']=$request->get('is_store_active');
            $store['store_type']=!empty($request->get('store_type'))?$request->get('store_type'):0;
            $store['created_at']=$dat;
            $store['updated_at']=$dat;
            $store_result=DB::table('crm_store')->insertGetId($store);
            $store_shopowner['store_id']=$store_result;
            $store_shopowner['shopowner_name']=!empty($request->get('shopowner_name'))?$request->get('shopowner_name'):0;
            $store_shopowner['shopowner_sex']=!empty($request->get('shopowner_sex'))?$request->get('shopowner_sex'):0;
            $store_shopowner['shopowner_age']=!empty($request->get('shopowner_age'))?$request->get('shopowner_age'):0;
            $store_shopowner['shopowner_mobile']=$request->get('shopowner_mobile');
            $store_shopowner['tell_phone']=$request->get('tell_phone');
            $store_shopowner['wechat']=$request->get('wechat');
            $store_shopowner['shopowner_affinity']=!empty($request->get('shopowner_affinity'))?$request->get('shopowner_affinity'):0;
            $store_shopowner['created_at']=$dat;
            $store_shopowner['updated_at']=$dat;
            $store_shopowner=DB::table('crm_msg_shopowner')->insert($store_shopowner);
            $store_message['store_id']=$store_result;
            $store_message['province']=!empty($request->get('province'))?$request->get('province'):0;
            $store_message['city']=!empty($request->get('city'))? $request->get('province'):0;
            $store_message['region']=!empty($request->get('region'))?$request->get('region'):0;
            $store_message['address']=$request->get('address');
            $store_message['personnel_num']=!empty($request->get('personnel_num'))?$request->get('personnel_num'):0;
            $store_message['personnel_acreage']=!empty($request->get('personnel_acreage'))?$request->get('personnel_acreage'):0;
            $store_message['around_store_num']=!empty($request->get('around_store_num'))?$request->get('around_store_num'):0;
            $store_message['daily_turnover']=!empty($request->get('daily_turnover'))?$request->get('daily_turnover'):0;
            $store_message['business_duration']=!empty($request->get('business_duration'))?$request->get('business_duration'):0;
            $store_message['coordination_type']=!empty($request->get('coordination_type'))?$request->get('coordination_type'):0;
            $store_message['shop_location']=!empty($request->get('shop_location'))?$request->get('shop_location'):0;
            $store_message['radiant_region']=!empty($request->get('radiant_region'))?$request->get('radiant_region'):0;
            $store_message['store_use_years']=!empty($request->get('store_use_years'))?$request->get('store_use_years'):0;
            $store_message['is_chain']=!empty($request->get('is_chain'))?$request->get('is_chain'):0;
            $store_message['replenishment_method']=!empty($request->get('replenishment_method'))?$request->get('replenishment_method'):0;
            $store_message['is_after_sale']=!empty($request->get('is_after_sale'))?$request->get('is_after_sale'):0;
            $store_message['shop_cleaning']=!empty($request->get('shop_cleaning'))?$request->get('shop_cleaning'):0;
            $store_message['is_app']=$request->get('is_app');
            $store_message['is_pos']=$request->get('is_pos');
           
            $store_message['created_at']=$dat;
            $store_message['updated_at']=$dat;
            $store_message['development_standard']=!empty($development_standard)?$development_standard:0;
            $store_message['store_expectations']=!empty($store_expectations)?$store_expectations:0;


            $store_message['is_express']=json_encode($is_express);


            $store_message['pile_head']=!empty($request->get('pile_head'))?$request->get('pile_head'):0;

            // $store_message['business_classification']=!empty($business_classification)?$business_classification:0;
            $store_message['business_classification']=json_encode($business_classification);

            $store_message['shelf_quantity']=!empty($request->get('shelf_quantity'))?$request->get('shelf_quantity'):0;
            $store_message['ice_num']=!empty($request->get('ice_num'))?$request->get('ice_num'):0;
            $store_message['cabinet_num']=!empty($request->get('cabinet_num'))?$request->get('cabinet_num'):0;
            $store_message['radiation_region_check_result'] = "";

            //  $store_message['store_thumb']=!empty($res['store_thumb'])?$res['store_thumb']:'';
            // $store_message['store_img']=!empty($res['store_img'])?$res['store_img']:'';
            // $store_message['business_license']=!empty($res['business_license'])?$res['business_license']:'';

            if($request->get('licenseA')!=''){
                $store_message['store_thumb']= [];
                $store_message['store_thumb'][] = $res['store_thumb'];
                $store_message['store_thumb'] = json_encode($store_message['store_thumb']);

            }
            if($request->get('licenseB')!=''||$request->get('licenseB')!=''){
                $store_message['store_img'] = [];;
                if(is_array($res['store_img']))
                {
                    $tmp_img = [];
                    foreach ($res['store_img'] as $key => $value) {
                        if(!empty($value))
                            $tmp_img[] = $value;
                    }
                    $store_message['store_img'] = $tmp_img;

                } else {
                    $store_message['store_img'][] = $res['store_img'];
                }

                $store_message['store_img'] = json_encode($store_message['store_img']);

            }
            if($request->get('licenseH')!=''||$request->get('licenseL')!=''){
                $store_message['business_license'] = [];
                if(is_array($res['store_img']))
                {
                    $store_message['business_license'] = $res['business_license'];
                    $tmp_img = [];
                    foreach ($res['business_license'] as $key => $value) {
                        if(!empty($value))
                            $tmp_img[] = $value;
                    }
                    $store_message['business_license'] = $tmp_img;

                } else {
                    $store_message['business_license'][] = $res['business_license'];
                }

                $store_message['business_license']=json_encode($store_message['business_license']);

            }




            $store_message['house_attribute'] = $request->get('house_attribute');
            $store_message['pile_head_remarks'] = $request->get('pile_head_remarks');
            if(empty($request->radiation_region_check_result))
            {
                $store_message['radiation_region_check_result'] = json_encode([]);
            } else {
                $store_message['radiation_region_check_result'] = json_encode($request->get('radiation_region_check_result'));
            }
            
            $store_message['store_code'] = $request->get('store_code');

            $store_message=DB::table('crm_store_message')->insert($store_message);
            $store_card['store_id']=$store_result;
            $store_card['door_card_name']=!empty($request->get('door_card_name'))?$request->get('door_card_name'):'';
            $store_card['main_long']=!empty($request->get('main_long'))?$request->get('main_long'):0;
            $store_card['main_height']=!empty($request->get('main_height'))?$request->get('main_height'):0;
            $store_card['left_long']=!empty($request->get('left_long'))?$request->get('left_long'):0;
            $store_card['right_long']=!empty($request->get('right_long'))?$request->get('right_long'):0;
            $store_card['left_height']=!empty($request->get('left_height'))?$request->get('left_height'):0;
            $store_card['right_height']=!empty($request->get('right_height'))?$request->get('right_height'):0;
            $store_card['acreage']=!empty($request->get('acreage'))?$request->get('acreage'):0;
            $store_card['material_quality']=!empty($request->get('material_quality'))?$request->get('material_quality'):0;
            $store_card['join_material_quality']=!empty($request->get('join_material_quality'))?$request->get('join_material_quality'):0;
            $store_card['created_at']=$dat;
            $store_card['updated_at']=$dat;
            $store_card_message=DB::table('crm_store_card_message')->insert($store_card);
            $datas['store_sn']=$store['store_sn'];
            $datas['admin_id']=$operator;
            $datas['is_active']=$request->get('is_store_active');
            $datas['ramk']=$store['ramk'];
            $res=$this->examine($datas);
            if($store_shopowner==false||$store_message==false||$store_card_message==false||$res==false){
                DB::rollBack();
                return redirect()->back()
                    ->withErrors("门店信息不完整,添加失败");
            }else{
                DB::commit();
                Event::fire(new permChangeEvent());
                event(new \App\Events\userActionEvent('\App\Models\Salesman\Store', $store_result, 1, '添加了一个门店:' . $request->get('store_name') . '(' . $store['ramk'] . ')'));
                return redirect('salesman/store/index')->withSuccess('添加成功！');
            }
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->getMessage());
        }
    }

    public function imgsUrl($store_data){
        $licenseA='';
        $licenseB='';
        $licenseF='';
        $licenseH='';
        $licenseL='';
        $up_dir = './img/';//存放路径
        if($store_data['licenseA']!=''){
            $licenseA=ImgToolClass::uplode($store_data['licenseA'],$up_dir);
        }
        if($store_data['licenseB']!=''){
            $licenseB=ImgToolClass::uplode($store_data['licenseB'],$up_dir);
        }
        if($store_data['licenseF']!=''){
            $licenseF=ImgToolClass::uplode($store_data['licenseF'],$up_dir);
        }
        if($store_data['licenseH']!=''){
            $licenseH=ImgToolClass::uplode($store_data['licenseH'],$up_dir);
        }
        if($store_data['licenseL']!=''){
            $licenseL=ImgToolClass::uplode($store_data['licenseL'],$up_dir);
        }

        // 判断是不是网络路径
        $prefix = "http://{$_SERVER['HTTP_HOST']}";
        if(false === strpos($licenseA, $prefix)&&!empty($licenseA))
        {
            $licenseA = $prefix."".$licenseA;
        }

        if(false === strpos($licenseB, $prefix)&&!empty($licenseB))
        {
            $licenseB = $prefix."".$licenseB;
        }

        if(false === strpos($licenseF, $prefix)&&!empty($licenseF))
        {
            $licenseF = $prefix.$licenseF;
        }

        if(false === strpos($licenseH, $prefix)&&!empty($licenseH))
        {
            $licenseH = $prefix.$licenseH;
        }

        if(false === strpos($licenseL, $prefix)&&!empty($licenseL))
        {
            $licenseL = $prefix.$licenseL;
        }

        $license1=array($licenseH,$licenseL);
        $license2=array($licenseB,$licenseF);

        $data['business_license']=str_replace("./", "/", $license1);
        $data['store_thumb']=str_replace("./", "/", $licenseA);
        $data['store_img']= str_replace("./", "/", $license2);
        return $data;
    }
    public function edit(Request $request,$id=0){
      if($request->getMethod()=="POST")
      {
        return $this->update($request);
        exit;
      }
        $result=(object)[];
        $rest=DB::table('crm_range')->select('id','name')->get();
        if($id>0){
            $where=['s.is_del'=>0,'s.id'=>$id];
            $result=DB::table('crm_store as s')
                ->leftjoin('crm_store_message as sm','s.id','=','sm.store_id')
                ->leftjoin('crm_msg_shopowner as msp','s.id','=','msp.store_id')
                ->leftjoin('crm_store_card_message as scm','s.id','=','scm.store_id')
                ->leftjoin('crm_store_examine as se','s.store_sn','=','se.store_sn')

                ->where($where)
                ->select('s.store_sn','s.store_type','s.store_name','s.store_old_name','s.sales_id','s.created_at','s.updated_at','s.ramk as rank','se.is_active as is_exam_active','sm.*','msp.*','scm.*')
                ->first();
            if(count($result)>0){
                $result->store_thumb=json_decode($result->store_thumb);
                if(!is_array($result->store_thumb))
                {
                    $result->store_thumb=["####"];
                }
                //$result->store_thumb=$result->store_thumb[0];

                $result->store_img=json_decode($result->store_img);
                if(!is_array($result->store_img))
                {
                    $result->store_img=["####"];
                }

                $result->store_img[0]=$result->store_img[0];
                if(isset($result->store_img[1]))
                {
                    $result->store_img[1]=$result->store_img[1];
                }else{
                    $result->store_img[1]="";
                }

                $result->business_license=json_decode($result->business_license);
                if(sizeof($result->business_license)<=0)
                {
                    $result->business_license=[""];

                }else{
                    $result->business_license[0]=$result->business_license[0];

                }
                if(isset($result->business_license[1]))
                {
                    $result->business_license[1]=$result->business_license[1];
                }else{
                    $result->business_license[1]="";
                }

                $result->business_classification=json_decode($result->business_classification);
                $result->radiation_region_check_result=json_decode($result->radiation_region_check_result);
                if(!is_array($result->radiation_region_check_result))
                {
                    $result->radiation_region_check_result=[];
                }

                $result->store_expectations=!empty($result->store_expectations)?explode(',',$result->store_expectations):array();
                $result->is_express=json_decode($result->is_express);
                $result->evelopment_standard=!empty($result->evelopment_standard)?explode(',',$result->evelopment_standard):array();
                $result->development_standard=!empty($result->development_standard)?explode(',',$result->development_standard):array();
            }else{
                return redirect()->back()
                    ->withErrors("门店不存在");
            }
        }

        
        $sales_id=Salesman::select('id','salens_name')->where(['is_del'=>0])->get();
        $message['sales_id']=$sales_id;
        $message['id']=$id;
        $message['data']=$result;
        return view('salesman.store.edit',compact('message','rest'));
    }

    public function update(Request $request){
        DB::beginTransaction();
        $dat=Carbon::now()->toDateTimeString();
        if(count($request->get('business_classification'))>0){
            $business_classification=json_encode($request->get('business_classification'));
        }
        if(count($request->get('store_expectations'))>0){
            $store_expectations=$request->get('store_expectations');
            $store_expectations=implode(',',$store_expectations);
        }
        if(count($request->get('is_express'))>0){
            $is_express=json_encode($request->get('is_express'));
        }
        if(count($request->get('development_standard'))>0){
            $development_standard=$request->get('development_standard');
            $development_standard=implode(',',$development_standard);
        }
        try{
            $loginTokenName = Auth::guard('admin')->getName();
            $operator=Session::get($loginTokenName);
            $store_data['licenseA']=$request->get('licenseA');
            $store_data['licenseB']=$request->get('licenseB');
            $store_data['licenseF']=$request->get('licenseF');
            $store_data['licenseH']=$request->get('licenseH');
            $store_data['licenseL']=$request->get('licenseL');
            $res=$this->imgsUrl($store_data);
            $store['store_sn']=$request->get('store_sn');
            $store['store_old_name']=$request->get('store_old_name');
            $store['store_name']=$request->get('store_name');
            $store['sales_id']=$request->get('sales_id');
            $store['ramk']=$request->get('store_ramk');
            $store['is_active']=$request->get('is_store_active');
            $store['store_type']=!empty($request->get('store_type'))?$request->get('store_type'):0;
            $store['updated_at']=$dat;
            $store_result=DB::table('crm_store')->where(array('id'=>$request->get('id')))->update($store);
            $store_shopowner['store_id']=$request->get('id');
            $store_shopowner['shopowner_name']=!empty($request->get('shopowner_name'))?$request->get('shopowner_name'):0;
            $store_shopowner['shopowner_sex']=!empty($request->get('shopowner_sex'))?$request->get('shopowner_sex'):0;
            $store_shopowner['shopowner_age']=!empty($request->get('shopowner_age'))?$request->get('shopowner_age'):0;
            $store_shopowner['shopowner_mobile']=$request->get('shopowner_mobile');
            $store_shopowner['tell_phone']=$request->get('tell_phone');
            $store_shopowner['wechat']=$request->get('wechat');
            $store_shopowner['shopowner_affinity']=!empty($request->get('shopowner_affinity'))?$request->get('shopowner_affinity'):0;
            $store_shopowner['updated_at']=$dat;
            $store_shop=DB::table('crm_msg_shopowner')->where(array('store_id'=>$request->get('id')))->update($store_shopowner);
            $store_message['store_id']=$request->get('id');
            $store_message['province']=!empty($request->get('province'))?$request->get('province'):0;
            $store_message['city']=!empty($request->get('city'))? $request->get('city'):0;
            $store_message['region']=!empty($request->get('region'))?$request->get('region'):0;
            $store_message['address']=$request->get('address');
            $store_message['personnel_num']=!empty($request->get('personnel_num'))?$request->get('personnel_num'):0;
            $store_message['personnel_acreage']=!empty($request->get('personnel_acreage'))?$request->get('personnel_acreage'):0;
            $store_message['around_store_num']=!empty($request->get('around_store_num'))?$request->get('around_store_num'):0;
            $store_message['daily_turnover']=!empty($request->get('daily_turnover'))?$request->get('daily_turnover'):0;
            $store_message['business_duration']=!empty($request->get('business_duration'))?$request->get('business_duration'):0;
            $store_message['coordination_type']=!empty($request->get('coordination_type'))?$request->get('coordination_type'):0;
            $store_message['shop_location']=!empty($request->get('shop_location'))?$request->get('shop_location'):0;
            $store_message['radiant_region']=$request->get('radiant_region');
            $store_message['store_use_years']=!empty($request->get('store_use_years'))?$request->get('store_use_years'):0;
            $store_message['is_chain']=!empty($request->get('is_chain'))?$request->get('is_chain'):0;
            $store_message['replenishment_method']=!empty($request->get('replenishment_method'))?$request->get('replenishment_method'):0;
            $store_message['is_after_sale']=!empty($request->get('is_after_sale'))?$request->get('is_after_sale'):0;
            $store_message['shop_cleaning']=!empty($request->get('shop_cleaning'))?$request->get('shop_cleaning'):0;
            $store_message['is_app']=$request->get('is_app');
            $store_message['is_pos']=$request->get('is_pos');

            if($request->get('licenseA')!=''){
                $store_message['store_thumb']= [];
                $store_message['store_thumb'][] = $res['store_thumb'];
                $store_message['store_thumb'] = json_encode($store_message['store_thumb']);

            }
            if($request->get('licenseB')!=''||$request->get('licenseB')!=''){
                $store_message['store_img'] = [];;
                if(is_array($res['store_img']))
                {
                    $tmp_img = [];
                    foreach ($res['store_img'] as $key => $value) {
                        if(!empty($value))
                            $tmp_img[] = $value;
                    }
                    $store_message['store_img'] = $tmp_img;

                } else {
                    $store_message['store_img'][] = $res['store_img'];
                }

                $store_message['store_img'] = json_encode($store_message['store_img']);

            }
            if($request->get('licenseH')!=''||$request->get('licenseL')!=''){
                $store_message['business_license'] = [];
                if(is_array($res['store_img']))
                {
                    $store_message['business_license'] = $res['business_license'];
                    $tmp_img = [];
                    foreach ($res['business_license'] as $key => $value) {
                        if(!empty($value))
                            $tmp_img[] = $value;
                    }
                    $store_message['business_license'] = $tmp_img;

                } else {
                    $store_message['business_license'][] = $res['business_license'];
                }

                $store_message['business_license']=json_encode($store_message['business_license']);

            }
            $store_message['updated_at']=$dat;
            $store_message['development_standard']=!empty($development_standard)?$development_standard:0;
            $store_message['store_expectations']=!empty($store_expectations)?$store_expectations:0;
            $store_message['is_express']=!empty($is_express)?$is_express:0;
            $store_message['pile_head']=!empty($request->get('pile_head'))?$request->get('pile_head'):0;
            $store_message['business_classification']=!empty($business_classification)?$business_classification:0;
            $store_message['shelf_quantity']=!empty($request->get('shelf_quantity'))?$request->get('shelf_quantity'):0;
            $store_message['ice_num']=!empty($request->get('ice_num'))?$request->get('ice_num'):0;
            $store_message['cabinet_num']=!empty($request->get('cabinet_num'))?$request->get('cabinet_num'):0;

            $store_message['radiation_region_check_result'] = json_encode($request->get('radiation_region_check_result'));
            $store_message['house_attribute'] = $request->get('house_attribute');
            $store_message['pile_head_remarks'] = $request->get('pile_head_remarks');
            $store_message['business_description'] = $request->get('business_description');
            $store_message['radiant_region']=$request->get('radiant_region');
            $store_message['store_code'] = $request->get('store_code');
            $store_message['is_dinghuo'] = $request->get('is_dinghuo');


            $store_mes=DB::table('crm_store_message')->where(array('store_id'=>$request->get('id')))->update($store_message);
            $store_card['store_id']=$request->get('id');
            $store_card['door_card_name']=$request->get('door_card_name');
            $store_card['main_long']=$request->get('main_long');
            $store_card['main_height']=$request->get('main_height');
            $store_card['left_long']=$request->get('left_long');
            $store_card['right_long']=$request->get('right_long');
            $store_card['left_height']=$request->get('left_height');
            $store_card['right_height']=$request->get('right_height');
            $store_card['acreage']=$request->get('acreage');
            $store_card['material_quality']=$request->get('material_quality');
            $store_card['join_material_quality']=$request->get('join_material_quality');
            $store_card['updated_at']=$dat;
            $store_card_message=DB::table('crm_store_card_message')->where(array('store_id'=>$request->get('id')))->update($store_card);
            // 检查是否有 
            $is_exist = DB::table('crm_store_examine')->where(['store_sn' =>$store['store_sn']])->get();
            $res = false;
            if(empty($is_exist[0]->store_sn))
            {
                $datas['store_sn']=$store['store_sn'];
                $datas['admin_id']=$operator;
                $datas['is_active']=$request->get('is_store_active');
                $datas['ramk']=$store['ramk'];
                $res=$this->examine($datas);
            }else{
                $datas['store_sn']=$store['store_sn'];
                $datas['is_active']=$request->get('is_store_active');
                $datas['ramk']=$store['ramk'];
                DB::table('crm_store_examine')->where(['store_sn' =>$store['store_sn']])->update($datas);
            }
            if($store_result==false&&$store_shop==false&&$store_mes==false&&$store_card_message==false&&$res==false){
                DB::rollBack();
                return redirect()->back()
                    ->withErrors("编辑失败,没有更新任何数据");
            }else{
                DB::commit();
                Event::fire(new permChangeEvent());
                event(new \App\Events\userActionEvent('\App\Models\Salesman\Store', $request->get('id'), 3, '修改了一个门店:' . $request->get('store_name') . '(' . $store['ramk'] . ')'));
                return redirect(route('salesman.store.index'))->withSuccess('编辑成功！');
            }
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->getMessage());
        }
    }

    public function destroy(int $id){
        if(!empty($id)){
            DB::beginTransaction();
            $store_name=DB::table('crm_store')->where('id','=',$id)->select('store_name')->first();
            try{
                DB::table('crm_store')->where('id','=',$id)->delete();
                DB::table('crm_msg_shopowner')->where('store_id','=',$id)->delete();
                DB::table('crm_store_card_message')->where('store_id','=',$id)->delete();
                DB::table('crm_store_message')->where('store_id','=',$id)->delete();
                DB::commit();
                Event::fire(new permChangeEvent());
                event(new \App\Events\userActionEvent('\App\Models\Salesman\Store', $id, 2, '删除了一个门店:' . $store_name->store_name . '(' . $store_name->store_name . ')'));
                return redirect('salesman/store/index')->withSuccess('删除成功！');
            }catch(\Exception $e){
                DB::rollBack();
                return redirect()->back()
                    ->withErrors($e->getMessage());
            }
        }else{
            return redirect()->back()
                ->withErrors("编号不能为空");
        }
    }

    public function details($id){
        if($id>0){
            $rest=DB::table('crm_range')->select('id','name')->get();
            $where=['s.is_del'=>0,'s.id'=>$id];
            $result=DB::table('crm_store as s')
                ->leftjoin('crm_store_message as sm','s.id','=','sm.store_id')
                ->leftjoin('crm_msg_shopowner as msp','s.id','=','msp.store_id')
                ->leftjoin('crm_store_card_message as scm','s.id','=','scm.store_id')
                ->where($where)->select('s.*','sm.*','msp.*','scm.*')->first();

            $result->store_thumb=substr(json_decode($result->store_thumb),1);
            $result->store_img=json_decode($result->store_img);
            $result->store_img[0]=!empty($result->store_img[0])?substr($result->store_img[0],1):'';
            $result->store_img[1]=!empty($result->store_img[1])?substr($result->store_img[1],1):'';
            $result->business_license=json_decode($result->business_license);
            $result->business_license[0]=!empty($result->business_license[0])?substr($result->business_license[0],1):'';
            $result->business_license[1]=!empty($result->business_license[1])?substr($result->business_license[1],1):'';
            $result->business_classification=!empty($result->business_classification)?explode(',',$result->business_classification):array();
            $result->store_expectations=!empty($result->store_expectations)?explode(',',$result->store_expectations):array();
            $result->is_express=!empty($result->is_express)?explode(',',$result->is_express):array();
            $result->evelopment_standard=!empty($result->evelopment_standard)?explode(',',$result->evelopment_standard):array();
            $result->development_standard=!empty($result->development_standard)?explode(',',$result->development_standard):array();
            $res_log=DB::table('crm_store_examine as a')->leftjoin('crm_store as b','a.store_sn','=','b.store_sn')->leftjoin('admin_users as c','a.examiner','=','c.id')->where('b.id','=',$id)->select('a.*','b.store_name','c.name')->get();
        }
        $sales_id=Salesman::select('id','salens_name')->get();
        $message['sales_id']=$sales_id;
        $message['id']=$id;
        $message['data']=$result;
        $message['log']=$res_log;
        return view('salesman.store.details',compact('message','rest'));
    }
    public function examine($data){
        try{
            if(!empty($data)){
                $dat=Carbon::now()->toDateTimeString();
                $examine_id='EX'.mt_rand(1000,9999999);
                $result=[
                    'examine_id'=>$examine_id,
                    'store_sn'=>$data['store_sn'],
                    'examiner'=>$data['admin_id'],
                    'is_active'=>$data['is_active'],
                    'ramk'=>$data['ramk'],
                    'created_at'=>$dat,
                    'updated_at'=>$dat
                ];
                $results=DB::table('crm_store_examine')->insert($result);
            }
        }catch(\Exception $e){
            return redirect()->back()
                ->withErrors($e->getMessage());
        }
        return $results;
    }
}
