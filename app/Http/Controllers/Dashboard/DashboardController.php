<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DataStatis;
use App\Models\Salesman\Salesman;

class DashboardController extends Controller
{
    public function index(Request $request){
        if($request->ajax()){
            $res=[];
            if($request->get('year')>0){
              $year=$request->get('year');
                $res['year']=$year;
            }
            if($request->get('month')>0){
                $month=$request->get('month');
                $res['month']=$month;
            }
            if($request->get('keywords')==0){
                $type=1;
            }elseif($request->get('keywords')==1){
                $type=3;
            }elseif($request->get('keywords')==2){
                $type=5;
            }
            if($request->get('seltype')=='a'){
                $data=DataStatis::set($type,$res);
            }else if($request->get('seltype')=='m'){

                $saletype=$request->get('saletype');
                $res['saletype']=$saletype;
                $data=DataStatis::store($type,$res);

            }else if($request->get('seltype')=='b'){

                $saletype=$request->get('saletype');
                $res['saletype']=$saletype;
                $data=DataStatis::Order($type,$res);
                $data=json_decode($data);
                $data=json_encode($data->message);

            }else if($request->get('seltype')=='o'){

                $saletype=$request->get('saletype');
                $res['saletype']=$saletype;
                $data=DataStatis::Order($type,$res);
                $data=json_decode($data);
                foreach($data->message as $k=>$v){
                    $data->message[$k]->sum=$v->order_amount;
                }
                $data=json_encode($data->message);
            }
            return $data;
        }
        $result=Salesman::where('is_del','=',0)->select('salens_name','id')->get();
        return view('dashboard.index',compact('result'));
    }
}
