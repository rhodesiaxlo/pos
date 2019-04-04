<?php

namespace App\Http\Controllers\Image;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Videos\TvRegin;
use DB;
use Carbon\Carbon;
use Event;
use App\Events\permChangeEvent;

class UploadmeController extends Controller
{

    public function index(Request $request){ 
        exit('index');
        $cid=0;
        $data=[];
        $message=[];
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
            if($columns[$k]['data']=='type_name'){
                $orderby_name='type';
            }else{
                $orderby_name=$columns[$k]['data'];
            }
            $result=DB::table('crm_tv_region as tr')
                ->leftjoin('crm_region as r','tr.province','=','r.region_id')
                ->leftjoin('crm_region as r1','tr.city','=','r1.region_id')
                ->leftjoin('crm_region as r2','tr.area','=','r2.region_id');
            if(!empty($search)){
                $result=$result->where(function($query)use($name){
                    $query->orwhere('province','LIKE','%'.trim($name).'%')
                        ->orwhere('city','LIKE','%'.trim($name).'%')
                        ->orwhere('area','LIKE','%'.trim($name).'%');
                });
            }
            $message['recordsFiltered']=$result->count();
            $result=$result->skip($start)->take($length);
            $result=$result->select('tr.type','tr.id','r.region_name as province ','r1.region_name as city','r2.region_name as area')->orderBy($orderby_name,$order_type)->get();
            foreach($result as $k=>$v){
                if($v->type==0){
                    $result[$k]->type_name='已开发';
                }elseif($v->type==1){
                    $result[$k]->type_name='待开发......';
                }else{
                    $result[$k]->type_name='开发中......';
                }
            }
            $message['data']=$result;
            $message['draw'] = $request->get('draw');
            $message['recordsTotal']=DB::table('crm_tv_region')->count();
            return response()->json($message);
        }
        return view('videos.tvwall.index',compact('cid','data'));
    }
    public function create(){
        $cid='';
        $region=DB::table('crm_region')->where('parent_id','=','1')->get();
        foreach ($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        $data['cid'] = $cid;
        $data['region']=$region;
        $data['result']=(object)$this->result;
        return view('videos.tvwall.create',$data);
    }
    public function store(Request $request){
        if($request->accepts('_token')){
            $admin_id=auth('admin')->user()->id;
            $dat=Carbon::now()->toDateTimeString();
            try{
                define('data',['admin_id'=>$admin_id,
                    'type'=>$request->get('type'),
                    'created_at'=>$dat,
                    'province'=>$request->get('province'),
                    'city'=>$request->get('city'),
                    'area'=>$request->get('area')]);
                $ID=TvRegin::insertGetId(data);
                $message['error']=1000;
                $message['msg']='添加成功';
                Event::fire(new permChangeEvent());
                event(new \App\Events\userActionEvent('\App\Models\Videos\TvRegin', $ID, 1, '新增了一个省:' . $request->get('province') . ')'));
            }catch(\Exception $e){
                $message['error']=1005;
                $message['msg']=$e->getMessage();
            }
            return response()->json($message);
        }
    }

    public function edit(int $id){
        $cid='';
        $region=DB::table('crm_region')->where('parent_id','=','1')->get();
        $result=TvRegin::where('id',$id)->first();
        foreach ($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        $data['cid'] = $cid;
        $data['region']=$region;
        $data['result']=$result;
        $data['data']=$data;
        return view('videos.tvwall.edit',$data);
    }

    public function update(Request $request){
        if($request->accepts('_token')){
            $admin_id=auth('admin')->user()->id;
            $dat=Carbon::now()->toDateTimeString();
            try{
                define('data',['admin_id'=>$admin_id,
                    'type'=>$request->get('type'),
                    'updated_at'=>$dat,
                    'province'=>$request->get('province'),
                    'city'=>$request->get('city'),
                    'area'=>$request->get('area')]);
                TvRegin::where('id','=',$request->get('id'))->update(data);
                $message['error']=1000;
                $message['msg']='编辑成功';
                Event::fire(new permChangeEvent());
                event(new \App\Events\userActionEvent('\App\Models\Videos\TvRegin', $request->get('id'), 1, '编辑了一个省:' . $request->get('province') . ')'));
            }catch(\Exception $e){
                $message['error']=1005;
                $message['msg']=$e->getMessage();
            }
            return response()->json($message);
        }
    }

    public function AjaxIndexs(Request $request){
        if($request->get('regiser_id')){
            $set=explode(',',$request->get('regiser_id'));
            $regier_id=$set;
            if(end($regier_id)==''){
                array_pop($regier_id);
            }
            //DB::enableQueryLog();
            $result=DB::table('crm_region')->select('region_id','region_name')->whereIn('parent_id',$regier_id)->get();
            //print_r(DB::getQueryLog());exit;
            if($result){
                $dara['error']=0;
                $dara['message']=$result;
            }else{
                $dara['error']=1;
                $dara['message']=[];
            }
        }else{
            $dara['error']=2;
            $dara['message']=[];
        }
        return json_encode($dara);
    }
    public function destroy(int $id){
      if($id){
        try{
            $result=TvRegin::where('id','=',$id)->select('province')->first();
            TvRegin::where('id','=',$id)->delete();
            Event::fire(new permChangeEvent());
            event(new \App\Events\userActionEvent('\App\Models\Videos\TvRegin', $id, 4, '删除了一个地区:' . $result->province . ')'));
            return redirect()->back()
                ->withSuccess("删除成功");
        }catch(\Exception $e){
            echo $e->getMessage();
            return redirect()->back()
                ->withErrors("删除失败");
        }
      }
    }

    public function test(Request $request)
    {
        exit('123');
    }
}
