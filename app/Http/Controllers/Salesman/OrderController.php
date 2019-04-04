<?php

namespace App\Http\Controllers\Salesman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Salesman\Salesman;
use Mockery\CountValidator\Exception;
use Event;
use App\Events\permChangeEvent;


class OrderController extends Controller
{
    const APP_KEY='B2A7BB41-C0D9-450F-981D-DFB3B241B3A3';
    protected static $api_list=['delete'=>'dsc.order.del.post','list'=>'dsc.order.list.get'];
    public function index(Request $request, $cid = 0){
        if ($request->ajax()) {
	        $url = 'https://dh168.shangding365.com/api.php';
            //$url = 'http://127.0.0.8/api.php';
	        $params['app_key'] = self::APP_KEY;
            $met=self::$api_list;
	        $params['method'] = $met['list'];
            $params['page_size']=$request->get('length');
            $da=$request->get('search');
            if($request->get('draw')!=3){
                $params['page'] = $request->get('draw');
            }else{
                $params['order_sn'] =trim($da['value']);
            }
            if(!empty($request->get('promoters'))){
                $params['promoters'] =$request->get('promoters');
            }
            /*var_dump($url.'?'.http_build_query($params));exit;*/
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

        $data['cid'] = (int)$cid;
        return view('salesman.order.index',$data);
    }

    public function orderStatus($orderstatus){
        $message='';
      switch($orderstatus)
      {
          case 0:
              $message='未支付';
              break;
          case 1:
          $message='已确认';
          break;
          case 2:
              $message='取消';
              break;
          case 3:
              $message='无效';
              break;
          case 4:
              $message='退货';
              break;
          case 5:
              $message='已分单';
              break;
          case 6:
              $message='部分分单';
              break;
          case 7:
              $message='部分已退货';
              break;
          case 8:
              $message='仅退款';
              break;
      }
        return $message;
    }

    public function setSale($promoters){
        $result=Salesman::where('id','=',$promoters)->select('salens_name')->first();
        if(!empty($result)){
            $result=$result->salens_name;
        }else{
            $result='无业务员';
        }
        return $result;
    }
    public function setUser($url,$result){
        $result['method']='dsc.user.info.get';
        $data = $this->curl($url, $result, 0, 1);
        $data = $data? json_decode($data, true): array();
        if($data['error']==1){
            $data='不存在';
        }else{
            $data=$data['info']['nick_name'];
        }
        return $data;
    }

     /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public function curl($url, $params = false, $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            }
            else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
    public function destroy(int $order_id){
    if($order_id>0){
      try{
          $url = 'https://dh168.shangding365.com/api.php';
          //$url = 'http://127.0.0.8/api.php';
          $params['app_key'] = self::APP_KEY;
          $met=self::$api_list;
          $params['method'] = $met['delete'];
          $params['order_id']=$order_id;
          $data = $this->curl($url, $params, 0, 1);
          $data = $data? json_decode($data, true): array();
          if($data['error']==0){
              Event::fire(new permChangeEvent());
              event(new \App\Events\userActionEvent(self::$api_list, $order_id, 2, '删除了一个订单:' . $order_id . '(' . $order_id . ')'));
              return redirect()->back()
                  ->withSuccess("删除成功");
          }else{
              return redirect()->back()
                  ->withErrors("删除失败");
          }
      }catch(\Exception $e){
         echo $e->getMessage();
      }
     }
    }
}