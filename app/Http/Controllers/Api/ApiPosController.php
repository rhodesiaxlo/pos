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

class ApiPosController extends Controller
{

   public function __construct()
   {
   }

    public function index(Request $request){
        
        return view('api.apipos.index');
    }

    /**********************************************************************************/
    /**
     * pos 机登录接口
     * uname 登录名
     * password 密码
     * device_no 设备硬件号，首次登录绑定硬件号，后续3要素验证，换机器需要后台手动重置硬件号
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function posLogin(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 获取本店的历史数据信息
     * 
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function pullHistoryData(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 中金支付 1402 反扫接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1402(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 中金支付 1811 （分页获取，每页数据不大于10,000条）
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1811(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 中金支付结算接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1341(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 同步会员数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncMember(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 同步用户数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncUser(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 同步商品数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncGoods(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 同步订单数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncOrder(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    public function syncShiftLog(Request $req)
    {
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 同步数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncData(Request $req)
    {
         return $this->ajaxFail([], 'not implement yet', 1000);
    }


    /**********************************************************************************/

    /**
     * json 正确返回
     * @param  [type] $data       [description]
     * @param  [type] $message    [description]
     * @param  [type] $error_code [description]
     * @return [type]             [description]
     */
    private function ajaxSuccess($data, $message, $error_code)
    {
        return response()->json($this->getSuccessArray($data, $message, $error_code));   
    }


    /**
     *  ajax 错误返回
     * @param  [type] $data       [description]
     * @param  [type] $message    [description]
     * @param  [type] $error_code [description]
     * @return [type]             [description]
     */
    private function ajaxFail($data, $message, $error_code)
    {
        return response()->json($this->getErrorArray($data, $message, $error_code));   
    }

    private function getErrorArray($data, $message="error", $error_code)
    {
        return $this->getArray($data, $message, $error_code, 0);
    }

    private function getSuccessArray($data, $message="success", $error_code)
    {
        return $this->getArray($data, $message, $error_code, 1);
    }

    private function getArray($data, $message, $error_code, $code)
    {
        $ret               = [];
        $ret['code']       = $code;
        $ret['error_code'] = $error_code;
        $ret['message']    = $message;
        $ret['data']       = $data;
        return $ret;
    }

}
