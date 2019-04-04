<?php

namespace App\Http\Controllers\Salesman;

use Faker\Provider\DateTime;
use App\Events\permChangeEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Salesman\Salesman;
use Carbon\Carbon;
use DB;
use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;

class SalesmanController extends Controller
{
    protected $fields = [
        'salens_name'  => '',
        'mobile'       => '',
        'sex'          => '保密',
        'cid'          => 0,
        'salens_type'  => '',
        'operator'     => '',
        'ramk'         =>'',
        'password'     => ''
    ];
    const SEXN='男';
    const SEXV='女';
    const SEXNV='保密';
    const APP_KEY='B2A7BB41-C0D9-450F-981D-DFB3B241B3A3';
    protected static $api_list=['delete'=>'dsc.order.del.post','list'=>'dsc.order.list.get'];
    public function __construct()
    {
        $this->sextn=self::SEXN;
        $this->sextv=self::SEXV;
        $this->sextnv=self::SEXNV;
    }

    public function index(Request $request,$cid=0){
        // $request->session()->put('success','value1');
        //exit(json_encode($request->session()->has('success')));

        if($request->ajax()){

            $cid=$request->get('cid',0);
            $start = $request->get('start');
            $length = $request->get('length');
            $order = $request->get('order');
            $columns = $request->get('columns');
            $search = $request->get('search');

            $search_value = $request->get('search_value');
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $k=$order[0]['column'];
            $name=$search['value'];
            $order_type=$order[0]['dir'];
            $orderby_name=$columns[$k]['data'];
            $result=DB::table('crm_salesman')->leftjoin('admin_users as u','crm_salesman.operator','=','u.id')->where('is_del','=','0');
            if(!empty($search)){
                $result=$result->where(function($query)use($name){
                    $query->orwhere('salens_name','LIKE','%'.trim($name).'%')
                        ->orwhere('sex','LIKE','%'.trim($name).'%')
                        ->orwhere('operator','LIKE','%'.trim($name).'%')
                        ->orwhere('mobile','LIKE','%'.trim($name).'%');
                });
            }
            if(!empty($search_value))
            {
              $result=$result->where(function($query)use($search_value){
                      $query->orwhere('salens_name','LIKE','%'.trim($search_value).'%')
                          ->orwhere('sex','LIKE','%'.trim($search_value).'%')
                          ->orwhere('operator','LIKE','%'.trim($search_value).'%')
                          ->orwhere('mobile','LIKE','%'.trim($search_value).'%');
                  }); 
            }

            if(!empty($start_date)&&!empty($end_date))
            {
              $result=$result->where(function($query)use($start_date, $end_date){
                      $query->whereBetween('add_time',array($start_date, $end_date));
                  });
            } elseif (!empty($start_date))
            {
              $result=$result->where(function($query)use($start_date){
                      $query->orwhere('add_time','>=',$start_date)
                          ;
                  });
            } elseif (!empty($end_date))
            {
              $result=$result->where(function($query)use($end_date){
                      $query->orwhere('add_time','=<',$end_date)
                          ;
                  });
            }else {

            }

            $message['recordsFiltered']=$result->count();
            $result=$result->skip($start)->take($length);
            //$result=$result->select('crm_salesman.*','u.name')->orderBy($orderby_name,$order_type)->get();
            $result=$result->select('crm_salesman.*','u.name')->orderBy("crm_salesman.add_time", "desc")->get();

            $message['data']=$result;
            $message['draw'] = $request->get('draw');
            $message['recordsTotal']=DB::table('crm_salesman')->where('is_del','=','0')->count();
            foreach($result as $k=>$v){
              if($v->salens_type==0){
                  $result[$k]->salens_type='星星自营业务员';
              }elseif($v->salens_type==1){
                  $result[$k]->salens_type='联营商业务员';
              }
            }
            return response()->json($message);
        }
        $message['cid']=(int)$cid;
        return view('salesman.sales.index',$message);
    }
    public function create(Request $request){ 

      if($request->getMethod()=="POST")
      {
        // 调用 store
        $this->store($request);
      }
        $cid=$request->get('cid');
        $data = [];
        foreach ($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        $data['cid'] = $cid;
        return view('salesman.sales.create',compact('data'));
    }


    public function edit(Request $request, int $cid=0){
      if($request->getMethod()=="PUT")
      {
        return $this->update($request);
        exit;
      }
        $data = [];
        $fields=DB::table('crm_salesman')->where('id','=',$cid)->first();
        $data=(array)$fields;
        $data['cid'] = $cid;
        return view('salesman.sales.edit',compact('data'));
    }
   public function update(Request $request){
      if($request->except('_token')){
          $loginTokenName = Auth::guard('admin')->getName();
          $operator=Session::get($loginTokenName);
          if($request->get('sex')==0){
              $sex=$this->sextn;
          }elseif($request->get('sex')==1){
              $sex=$this->sextv;
          }else{
              $sex=$this->sextnv;
          }


          $cid=$request->get('id');
          try{
              $dat=Carbon::now()->toDateTimeString();
              define('result',['salens_name'=>$request->get('name'),
                  'mobile'=>$request->get('mobile'),
                  'sex'=>$sex,
                  'salens_type'=>$request->get('salens_type'),
                  'update_time'=>$dat,
                  'operator'=>$operator,
                  'password' =>$request->get('password'),
                  'ramk'=>$request->get('ramk')]);


              if(Salesman::where('id','=',$cid)->update(result)==false){
                  return redirect()->withErrors('修改失败！');
              }else{
                  Event::fire(new permChangeEvent());
                  event(new \App\Events\userActionEvent('\App\Models\Salesman\Salesman', $cid, 3, '修改了一个业务员:' . $request->get('name') . '(' . $request->get('ramk') . ')'));
                  return redirect(route('salesman.sales.index'))->withSuccess('修改成功！');
              }
          }catch(Exception $e){
              echo $e->getMessage();
          }
      }else{
          return redirect()->withErrors('数据不能为空！');
      }
   }


   /**
    * 保存业务员
    * @param  Request $request [description]
    * @return [type]           [description]
    */
    public function store(Request $request){
       if($request->except('_token')&&$request->get('name')){
           $loginTokenName = Auth::guard('admin')->getName();
           $operator=Session::get($loginTokenName);

           // 验证密码是否一致，如果不一致，提示
           // todo 
           if(trim($request->get('password'))!=trim($request->get('confirm_password')))
           {
            exit(json_encode(['code'=>"两次密码不一致",'message'=>"两次密码不一致",'error_code'=>0]));
           }
           
           // 检查是否有重复的业务员，如果有，提示
           $list = DB::table('crm_salesman')->where(['mobile'=>$request->get('mobile')])->get();
           if(!empty($list[0]->id))
           {

            if($list[0]->is_del==0)
            {
              exit(json_encode(['code'=>"已经存在相同手机号的业务员",'message'=>"已经存在相同手机号的业务员",'error_code'=>0]));
            }
              // 找回业务员
              $res = Salesman::where(['id'=>$list[0]->id])->update(['is_del'=>0]);
              if(false !== $res)
              {
                exit(json_encode(['error_code'=>1, 'code'=>"找回业务员成功!", 'message'=>"找回业务员成功!"]));
              }
              
              //exit(json_encode(['code'=>"已经存在相同手机号的业务员"]));
           }else{
           }
           
           if($request->get('sex')==0){
               $sex=$this->sextn;
           }elseif($request->get('sex')==1){
               $sex=$this->sextv;
           }else{
               $sex=$this->sextnv;
           }

           try{
               $dat=Carbon::now()->toDateTimeString();
               $result=['salens_name'=>$request->get('name'),
                   'mobile'=>$request->get('mobile'),
                   'sex'=>$sex,
                   'salens_type'=>$request->get('salens_type'),
                   'add_time'=>$dat,
                   'update_time'=>$dat,
                   'operator'=>$operator,
                   'ramk'=>$request->get('ramk'),
                   'password' =>$request->get('password')];
                $setresult=Salesman::insertGetId($result);
               if($setresult>0){
                   $data['message']='创建成功';
                   $data['error_code'] = 1;

               }else{
                   $data['message']='创建失败';
                   $data['error_code'] = 0;
               }
           }catch(Exception $e){
             $data['message']=$e->getMessage();
             $data['error_code'] = 0;

           }
       }else{
          $data['message']='参数错误';
          $data['error_code'] = 0;

       }
        Event::fire(new permChangeEvent());
        event(new \App\Events\userActionEvent('\App\Models\Salesman\Salesman', $setresult, 1, '新增了一个业务员:' . $request->get('name') . '(' . $request->get('ramk') . ')'));
        return json_encode($data);
    }

    public function destroy(Request $request,int $cid){
       if(!empty($cid)){
         if(Salesman::where('id','=',$cid)->update(array('is_del'=>'1'))==false){
             return redirect()->back()
                 ->withErrors("删除失败");
         }else{
             Event::fire(new permChangeEvent());
             event(new \App\Events\userActionEvent('\App\Models\Salesman\Salesman', $cid, 2, '删除了一个业务员:' . $cid . '(' . $cid . ')'));
             return redirect()->back()
                 ->withSuccess("删除成功");
         }
       }else{
           return redirect()->back()
               ->withErrors("编号不能为空");
       }
    }
    public function details(Request $request,int $id):void{
      if($id>0){
       $selesMessage=DB::table('crm_salesman as s')->leftjoin('admin_users as u','s.operator','=','u.id')->where('s.id','=',$id)->select('s.id','s.salens_name','s.mobile','s.sex','s.salens_type','s.add_time','u.name','s.ramk')->first();
          if($selesMessage->salens_type=='0'){
              $selesMessage->salens_type='星星业务员';
          }elseif($selesMessage->salens_type=='1'){
              $selesMessage->salens_type='联营商业务员';
          }
          $data['seale']=$selesMessage;//业务员详情

      }
    }
    //业务员店铺
    public function StoreList(Request $request){
        if($request->ajax()){
            $id=$request->get('id');
            $start = $request->get('start');
            $length = $request->get('length');
            $order = $request->get('order');
            $columns =$request->get('columns');
            $k=$order[0]['column'];
            $order_type=$order[0]['dir'];
            $orderby_name=$columns[$k]['data'];
            $seStore=DB::table('crm_store as s')
                ->leftjoin('crm_msg_shopowner as ms','s.id','=','ms.store_id')
                ->leftjoin('crm_store_examine as se','s.store_sn','=','se.store_sn')
                ->leftjoin('crm_store_message as sm','s.id','=','sm.store_id')
                ->where('s.id','=',$id);
            $message['recordsFiltered']=$seStore->count();
            $seStore=$seStore->skip($start)->take($length);
            $seStore=$seStore->select('s.store_sn','s.store_name','sm.province','sm.city','sm.region','sm.address','ms.shopowner_mobile','s.created_at','s.ramk','se.is_active')
                ->orderBy($orderby_name,$order_type)->get();
            $message['data']=$seStore;
            $message['draw'] = $request->get('draw');
            $message['recordsTotal']=DB::table('crm_store')->count();
            return response()->json($message);
        }
    }
    //业务员订单
    public function SealeOrder(Request $request){
        if ($request->ajax()) {
            //$url = 'https://dh168.shangding365.com/api.php';
            $url = 'http://127.0.0.8/api.php';
            $params['app_key'] = self::APP_KEY;
            $met=self::$api_list;
            $params['method'] = $met['list'];
            $params['promoters'] = $request->get('id');
            $params['page_size']=$request->get('length');
            $da=$request->get('search');
            if($request->get('draw')!=3){
                $params['page'] = $request->get('draw');
            }else{
                $params['order_sn'] =trim($da['value']);
            }
            $data = $this->curl($url, $params, 0, 1);
            $data = $data? json_decode($data, true): array();
            $list = !empty($data['info']['list'])? $data['info']['list']: array();
            foreach ($list as $key => $val) {
                $list[$key]['add_time'] = date('Y/m/d H:i:s', $val['add_time']);
                $params['user_id']=$val['user_id'];
                $list[$key]['promoters']=$this->setSale($val['promoters']);
                $list[$key]['order_status']=$this->orderStatus($val['order_status']);
                $list[$key]['user_name']=$this->setUser($url,$params);
            }
            $message['data'] = $list;
            $message['draw'] = $request->get('draw');
            $message['recordsTotal'] = isset($data['info']['record_count'])? $data['info']['record_count']: 0;
            $message['recordsFiltered']=isset($data['info']['record_count'])? $data['info']['record_count']: 0;
            //dd($message);
            return response()->json($message);
        }
    }
}
