<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-28
 * Time: 11:40
 */

namespace App\Tool\Statistics\Services;
use App\Models\Salesman\Salesman;
use Carbon\Carbon;
use DB;
use App\Models\Salesman\Store;
use App\Tool\HttpSet\HttpCurl;

class DataStatis
{
    /**
     * 业务员统计
     */
   public function set($type,$result){
         $select='';
         $order='';
         $group='';
         $yaer=!empty($result['year'])?$result['year']:2018;
         $mount=!empty($result['month'])?$result['month']:9;
         $where['is_del']=0;
         //DB::enableQueryLog();
         $yuerStore=Salesman::where($where);
         //按年
         if($type==1){
             $group=DB::raw("YEAR(add_time)");
             $order=DB::raw("YEAR(add_time)");
             $select=DB::raw('count(id) as sum,YEAR(add_time)as createTime');
         }elseif($type==2){
             //按季度
             $group=DB::raw("QUARTER(add_time)");
             $order=DB::raw("QUARTER(add_time)");
             $select=DB::raw('count(id) as sum,QUARTER(add_time) as createTime');
             $yuerStore=$yuerStore->select($select)->where(DB::raw('YEAR(add_time)'),'=',$yaer);
         }elseif($type==3){
             //按月
             $group=DB::raw("MONTH(add_time)");
             $order=DB::raw("MONTH(add_time)");
             $select=DB::raw('count(id) as sum,MONTH(add_time) as createTime');
             $yuerStore=$yuerStore->where(DB::raw('YEAR(add_time)'),'=',$yaer);
         }elseif($type==4){
             //按周
             $group=DB::raw("WEEK(add_time)");
             $order=DB::raw("WEEK(add_time)");
             $select=DB::raw('count(id) as sum,WEEK(add_time) as createTime');
             $yuerStore=$yuerStore->where(DB::raw('YEAR(add_time)'),'=',$yaer)->where(DB::raw('MONTH(add_time)'),'=',$mount);
         }elseif($type==5){
             //按日
             $group=DB::raw("DATE(add_time)");
             $order=DB::raw("DATE(add_time)");
             $select=DB::raw('count(id) as sum,DATE(add_time) as createTime');
             $yuerStore=$yuerStore->where(DB::raw('YEAR(add_time)'),'=',$yaer)->where(DB::raw('MONTH(add_time)'),'=',$mount);
         }
         $yuerStore=$yuerStore->orderBy($order)->groupBy($group)->select($select)->get();
         return json_encode($yuerStore);
   }

    public function store($type,$result){
        $select='';
        $order='';
        $group='';
        $yaer='2018';
        $mount='9';
        $where['is_del']=0;
        if($result['saletype']>0){
            $where['sales_id']=$result['saletype'];
        }
        DB::enableQueryLog();
        $yuerStore=Store::where($where);
        //按年
        if($type==1){
            $group=DB::raw("YEAR(created_at)");
            $order=DB::raw("YEAR(created_at)");
            $select=DB::raw('count(id) as sum,YEAR(created_at) as createTime');
        }elseif($type==2){
            //按季度
            $group=DB::raw("QUARTER(created_at)");
            $order=DB::raw("QUARTER(created_at)");
            $select=DB::raw('count(id) as sum,QUARTER(created_at) as createTime');
            $yuerStore=$yuerStore->select($select)->where(DB::raw('YEAR(created_at)'),'=',$yaer);
        }elseif($type==3){
            //按月
            $group=DB::raw("MONTH(created_at)");
            $order=DB::raw("MONTH(created_at)");
            $select=DB::raw('count(id) as sum,MONTH(created_at) as createTime');
            $yuerStore=$yuerStore->where(DB::raw('YEAR(created_at)'),'=',$yaer);
        }elseif($type==4){
            //按周
            $group=DB::raw("WEEK(created_at)");
            $order=DB::raw("WEEK(created_at)");
            $select=DB::raw('count(id) as sum,WEEK(created_at) as createTime');
            $yuerStore=$yuerStore->where(DB::raw('YEAR(created_at)'),'=',$yaer)->where(DB::raw('MONTH(created_at)'),'=',$mount);
        }elseif($type==5){
            //按日
            $group=DB::raw("DATE(created_at)");
            $order=DB::raw("DATE(created_at)");
            $select=DB::raw('count(id) as sum,DATE(created_at) as createTime');
            $yuerStore=$yuerStore->where(DB::raw('YEAR(created_at)'),'=',$yaer)->where(DB::raw('MONTH(created_at)'),'=',$mount);
        }
        $yuerStore=$yuerStore->orderBy($order)->groupBy($group)->select($select)->get();
        return json_encode($yuerStore);

    }
   public function Order($type,$result){
        $url='https://dh168.shangding365.com/admin/order.php?act=order_total';
        $select='';
        $order='';
        $group='';
        $yaer='2018';
        $mount='9';
        $where['is_del']=0;
        if($result['saletype']>0){
            $sale=$result['saletype'];
            $result['user_id']=$sale;
        }
        //按年
        if($type==1){
            $group="YEAR(created_at)";
            $type='YEAR';
        }elseif($type==2){
            //按季度
            $group="QUARTER(created_at)";
            $type='QUARTER';
        }elseif($type==3){
            //按月
            $group="MONTH(created_at)";
            $type='MONTH';
            if(!empty($result['year'])&&$result['year']>0){
                $yaer=$result['year'];
            }
        }elseif($type==4){
            //按周
            $group="WEEK(created_at)";
            $type='WEEK';
        }elseif($type==5){
            //按日
            $group="DATE(created_at)";
            $type='DATE';
            if(!empty($result['year'])&&$result['year']>0){
                $yaer=$result['year'];
            }
            if(!empty($result['month'])&&$result['month']>0){
                $mount=$result['month'];
            }
        }
       $result['where']=$group;
       $result['type']=$type;
       $result['year']=$yaer;
       $result['month']=$mount;
       $data=HttpCurl::curl($url,$result,1,1);
       $data = $data? json_decode($data, true): array();
       return json_encode($data);
    }
}