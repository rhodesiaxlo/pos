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

use App\Models\Pos\User;
use App\Models\Pos\Member;
use App\Models\Pos\StoreGoodsSku;
use App\Models\Pos\Category;
use App\Models\Pos\Goods;
use App\Models\Pos\Order;
use App\Models\Pos\OrderGoods;
use App\Models\Pos\ShiftLog;


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

        $name = $req->get('name');
        $password = $req->get('password');
        $device_no = $req->get('device_no');

        if(empty($name))
        {
            return $this->ajaxFail([], "name can not be empty", 1000);
        }
        
        // 密码不能为空
        if(empty($password))
        {
            return $this->ajaxFail([], "password can not be empty", 1001);
        }
        
        // device_no 不能为空
        if(empty($device_no))
        {
            return $this->ajaxFail([], "device_no can not be empty", 1002);
        }

        $where['uname'] = $name;
        $where['password'] = $password;
        $result = User::where($where)->first();
        if(empty($result))
        {
            return $this->ajaxFail([], 'name and password conbination incorrect, please try again', 1003);
        }

        if(!empty($result->device_no) &&trim($result->device_no) != trim($device_no))
        {
            return $this->ajaxFail([], 'device_no not match , please contract custerm service to unbinding', 1004);
        }

        // 登录成功，设备硬件号匹配完成
        if(empty($result->device_no))
        {
            // 关联硬件号
            $result->device_no = $device_no;
            if($result->save() === false)
            {
                return $this->ajaxFail([], 'device_no currently empty ,bingding with no success', 1005);
            }

            return $this->ajaxSuccess(['userinfo'=>$result], "success");
        }

        return $this->ajaxSuccess(['userinfo'=>$result], "success");
        // return $this->ajaxFail([], 'not implement yet', 1000);
    }


    /**
     * 获取本店的历史数据信息
     * 
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function pullHistoryData(Request $req)
    {
        $store_code = $req->get('store_code');
        if(empty($store_code))
        {
            return $this->ajaxFail([], "store_code can not be empty", 1000);
        }

        // 需要同步的信息
        // 用户信息
        $where['store_code'] = $store_code;
        $userinfo =User::where($where)->get();
        if(empty($userinfo[0]))
        {
            return $this->ajaxFail([], "store not found", 1001);   
        }
        // 会员信息
        $memberinfo = Member::where($where)->get();
        // category
        $category = Category::all();
        // store_goods_sku
        $storeGoodsSkuInfo = StoreGoodsSku::where($where)->get();
        // goods
        $goodsinfo = Goods::where($where)->get();
        // order
        $orderinfo = Order::where($where)->get();
        // order goods
        $ordergoodsinfo = OrderGoods::where($where)->get();
        // shiftlog
        $shiftloginfo = ShiftLog::where($where)->first();

        $ret = [];
        $ret['category'] = $category;
        $ret['user'] = $userinfo;
        $ret['member'] = $memberinfo;
        $ret['goods_sku'] = $storeGoodsSkuInfo;
        $ret['goods'] = $goodsinfo;
        $ret['order'] = $orderinfo;
        $ret['order_goods'] = $ordergoodsinfo;
        $ret['shift_log'] = $shiftloginfo;
        return $this->ajaxSuccess($ret, "success");

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
        $store_code = $req->get('store_code');

        //  解析参数
        
        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    public function syncGoodsSku(Request $req)
    {
        $store_code = $req->get('store_code');

        //  解析参数
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

    public function syncOrderGoods(Request $req)
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
        define('SYNC_USER' ,1);
        define('SYNC_MEMBER', 2);
        define('SYNC_GOODSSKU', 3);
        define('SYNC_GOODS', 4);
        define('SYNC_ORDER', 5);
        define('SYNC_ORDERGOODS', 6);
        define('SYNC_SHIFTLOG', 7);

        $store_code = $req->get('store_code');
        $type = $req->get('type');

        if(empty($store_code))
        {
            return $this->ajaxFail([], 'store_code can not be empty', 1000);
        }

        if(empty($type))
        {
            return $this->ajaxFail([], 'type can not be empty, 1 user, 2 member, 3 goods_sku 4 goods, 5 order 6 order goods, 7 shift log', 1001);
        }

        if(intval($type) === false)
        {
            return $this->ajaxFail([], 'type variable type illegal', 1002);   
        }

        if( intval($type)<1 || intval($type)>6)
        {
            return $this->ajaxFail([], 'type value type illegal', 1003);        
        }

        // store_code 不存在
        $is_exist = User::where(['store_code'=>$store_code])->first();
        if(empty($is_exist))
        {
            return $this->ajaxFail([], 'store not found', 1004);   
        }

        switch (intval($type)) {
            case SYNC_USER:
                return $this->syncUser($req);
                break;
            case SYNC_MEMBER:
                return $this->syncMember($req);
                break;
            case SYNC_GOODSSKU:
                return $this->syncGoodsSku($req);
                break;
            case SYNC_GOODS:
                return $this->syncGoods($req);
                break;
            case SYNC_ORDER:
                return $this->syncOrder($req);
                break;
            case SYNC_ORDERGOODS:
                return $this->syncOrderGoods($req);
                break;
            case SYNC_SHIFTLOG:
                return $this->syncShiftLog($req);
                break;
            default:
                # code...
                break;
        }

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
    private function ajaxSuccess($data, $message, $error_code=0)
    {
        //exit(json_encode($this->getErrorArray($this->getSuccessArray($data, $message, $error_code))));
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
        //exit(json_encode($this->getErrorArray($data, $message, $error_code)));
        return response()->json($this->getErrorArray($data, $message, $error_code));   
    }

    private function getErrorArray($data, $message="error", $error_code=0)
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
