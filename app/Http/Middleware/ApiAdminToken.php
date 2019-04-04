<?php

namespace App\Http\Middleware;

use Closure;

class ApiAdminToken
{
    const APPID=5288971;
    const APPSERVER= 'r5e2t85tyu142u665698fzu';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*$req=$this->set();
        $date=json_decode($req);
        if($date->msg=='10000'){
            return $next($request);
        }else{
           dd($req);
        }*/
        return $next($request);
    }
    protected function set(){
        $array=[
            'appid'=>self::APPID,
            'menu'=>'客户服务列表',
            'lat'=>21.223,
            'lng'=>131.334,
            'time'=>1538099581
        ];
        foreach($array as $k=>$v){
            $arr[$k]=$k;
        }
        sort($arr);
        $str='';
        foreach($arr as $k=>$v){
            $str=$str.$arr[$k].$array[$v];
        }
        $restr=$str.self::APPSERVER;
        $sign = strtoupper(sha1($restr));
        $array['sign']=$sign;
        return $this->get($array);
    }

    protected function get($array){
        $clientSign=$array['sign'];
        unset($array['sign']);
        foreach($array as $k=>$v){
            $arr[$k]=$k;
        }
        sort($arr);
        $str='';
        foreach($arr as $k=>$v){
            $str=$str.$arr[$k].$array[$v];
        }
        $restr=$str.self::APPSERVER;
        $sign = strtoupper(sha1($restr));
        if($sign==$clientSign){
            $code['msg']='10001';
            $code['message']=$array;
        }else{
            $code['msg']='10005';
            $code['message']=$array;
        }
        return json_encode($code);
    }
}
