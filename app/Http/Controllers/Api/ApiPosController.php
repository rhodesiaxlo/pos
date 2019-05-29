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
use App\Models\Pos\Bank;
use App\Models\Pos\Region;
use App\Models\Pos\ServerOrder;
use App\Models\Pos\OutflowLog;
use App\Models\Pos\Prepayment;
use App\Models\Pos\Postpayment;
use App\Models\Pos\GoodsSku;
use App\Models\Pos\GeneralLog;
use App\Models\Pos\GoodsImport;
use App\Models\Pos\Supplier;
use App\Models\Pos\InOutStockLog;





use App\Models\Pos\AbnormalTransactionLog;




use App\Models\Pos\CpccTxLog;
use App\Models\Pos\CpccDownloadLog;




class ApiPosController extends Controller
{

    private $cpcc_url = "";
    private $cpcc_url_dev = "https://test.cpcn.com.cn/Gateway/InterfaceII";
    private $cpcc_url_pro = "https://www.china-clearing.com/Gateway/InterfaceII";
    private $dev_institution_id = "004792";
    private $pro_institution_id = "000547";
    private $institution_id = "";

    const SYNC_USER = 1;
    const SYNC_MEMBER = 2;
    const SYNC_GOODSSKU = 3;
    const SYNC_GOODS = 4;
    const SYNC_ORDER =5;
    const SYNC_ORDERGOODS = 6;
    const SYNC_SHIFTLOG = 7;
    const SYNC_CATEGORY = 8;
    const SYNC_IMPOTEDGOODS = 9;
    const SYNC_SUPPLIER = 10;
    const SYNC_INOUTSTOCK = 11;


    // 支付方式
    const PAYWAY_WECHAT = 11;
    const PAYWAY_ALI = 21;
    const PAYWAY_UNIPAY = 31;

    // 支付场景
    const PAYMENTSCENE_BARCODE = 10;
    const PAYMENTSCENE_WAVE = 20;
    
    // 订单状态
    const ORDERSTATUS_UNKNOWN = 10;
    const ORDERSTATUS_SUCCESS = 20;
    const ORDERSTATUS_FAIL = 30;

    // 卡类型
    const CARDTYPE_BALANCE = 10;
    const CARDTYPE_DEPOSIT = 20;
    const CARDTYPE_CREDIT = 30;
    const CARDTYPE_UNKNOWN = 40;

    // 
    const CHECK_SUCCESS=0;
    const CHECK_NUMBERNOTMATCH=1;
    const CHECK_ORDERNOT = 2;
    const check_CCPCNOT = 3;



   public function __construct()
   {
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $this->cpcc_url = $this->cpcc_url_pro;
            $this->institution_id = $this->pro_institution_id;
        } else {
            $this->cpcc_url = $this->cpcc_url_dev;
            $this->institution_id = $this->dev_institution_id;
        }
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
            return $this->ajaxFail(null, "name can not be empty", 1000);
        }
        
        // 密码不能为空
        if(empty($password))
        {
            return $this->ajaxFail(null, "password can not be empty", 1001);
        }
        
        // device_no 不能为空
        if(empty($device_no))
        {
            return $this->ajaxFail(null, "device_no can not be empty", 1002);
        }

        $where['uname'] = $name;
        $where['password'] = $password;

        $result = User::where($where)->first();
        if(empty($result))
        {
            return $this->ajaxFail(null, 'name and password conbination incorrect, please try again', 1003);
        }

        if(intval($result['rank'])!==0)
        {
            return $this->ajaxFail(null, 'account illegal', 1006);
        }

        if($result['is_active'] == 0)
        {
            return $this->ajaxFail(null, 'account is not active', 1016);   
        }

        if($result['deleted'] == 1)
        {
            return $this->ajaxFail(null, 'account has been deleted', 1018);   
        }

        if(!empty($result->device_no) &&trim($result->device_no) != trim($device_no))
        {
            return $this->ajaxFail(null, 'device_no not match , please contract custerm service to unbinding', 1004);
        }

        // 登录成功，设备硬件号匹配完成
        if(empty($result->device_no))
        {
            // 关联硬件号
            $result->device_no = $device_no;
            if($result->save() === false)
            {
                return $this->ajaxFail(null, 'device_no currently empty ,bingding with no success', 1005);
            }

            $tmp[] = $result;

            return $this->ajaxSuccess($tmp, "success");
        }

        $tmp[] = $result;
        return $this->ajaxSuccess($tmp, "success");
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
        $type = $req->get('type');
        if(empty($store_code))
        {
            return $this->ajaxFail(null, "store_code can not be empty", 1000);
        }

        if(empty($type))
        {
            return $this->ajaxFail(null, "type can not be empty", 1002);
        }

        if(intval($type)===false || intval($type)> self::SYNC_INOUTSTOCK || intval($type) < self::SYNC_USER)
        {
            return $this->ajaxFail(null, "type value illegal", 1003);
        }

        // 需要同步的信息
        // 用户信息
        $where['store_code'] = $store_code;
        $userinfo =User::where($where)->get();

        if(empty($userinfo[0]))
        {
            return $this->ajaxFail(null, "store not found", 1001);   
        }
        if(intval($type) == self::SYNC_USER)
        {
            return $this->ajaxSuccess($userinfo, "success");
        }

        if(intval($type) == self::SYNC_MEMBER)
        {
            // 会员信息
            $memberinfo = Member::where($where)->get();
            return $this->ajaxSuccess($memberinfo, "success");
        }

        if(intval($type) == self::SYNC_CATEGORY)
        {
            // category
            $category = Category::all();
            return $this->ajaxSuccess($category, "success");
        }

        if(intval($type) == self::SYNC_GOODSSKU)
        {

            // store_goods_sku
            $storeGoodsSkuInfo = StoreGoodsSku::where($where)->get();
            return $this->ajaxSuccess($storeGoodsSkuInfo, "success");
        }

        if(intval($type) == self::SYNC_GOODS)
        {
            // goods
            $goodsinfo = Goods::where($where)->get();
            return $this->ajaxSuccess($goodsinfo, "success");
        }

        if(intval($type) == self::SYNC_ORDER)
        {
            // order
            $orderinfo = Order::where($where)->get();
            return $this->ajaxSuccess($orderinfo, "success");
        }

        if(intval($type) == self::SYNC_ORDERGOODS)
        {
            // order goods
            $ordergoodsinfo = OrderGoods::where($where)->get();
            return $this->ajaxSuccess($ordergoodsinfo, "success");
        }

        if(intval($type) == self::SYNC_SHIFTLOG)
        {
            // shiftlog
            $shiftloginfo = ShiftLog::where($where)->get();
            return $this->ajaxSuccess($shiftloginfo, "success");
        }

        if(intval($type) == self::SYNC_IMPOTEDGOODS)
        {
            // shiftlog
            $where1['store_code'] = $store_code;
            $where1['is_synced'] = 0;
            $importgoodslist = GoodsImport::where($where1)->get();
            return $this->ajaxSuccess($importgoodslist, "success");
        }

        if(intval($type) == self::SYNC_SUPPLIER)
        {
            $shiftloginfo = Supplier::where($where)->get();
            return $this->ajaxSuccess($shiftloginfo, "success"); 
        }

        if(intval($type) == self::SYNC_INOUTSTOCK)
        {
            $shiftloginfo = InOutStockLog::where($where)->get();
            return $this->ajaxSuccess($shiftloginfo, "success"); 
        }

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
     * 导入商品数据通知
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function importNotify(Request $req)
    {
        $store_code = $req->get('store_code');
        $goods_sn_list = $req->get('goods_sn_list');
        if(empty($store_code))
        {
            return $this->ajaxFail(null, "store_code can not be empty", 1000);
        }

        if(empty($goods_sn_list))
        {
            return $this->ajaxFail(null, "goods_sn_list can not be empty", 1000);
        }        

        // 需要同步的信息
        // 用户信息
        $where['store_code'] = $store_code;
        $userinfo =User::where($where)->get();

        if(empty($userinfo[0]))
        {
            return $this->ajaxFail(null, "store not found", 1001);   
        }

        if(!is_array($goods_sn_list))
        {
            return $this->ajaxFail(null, "goods_sn_list has to be array", 1000);
        }


        // 更新
        $list = GoodsImport::where(['store_code'=>$store_code,'is_synced'=>0])
                    ->whereIn('goods_sn',$goods_sn_list)->get();
        DB::beginTransaction();
        try {
            foreach ($list as $key => $value) {
                $value->is_synced = 1;
                $rr = $value->save();
                if($rr !==false)
                {

                } else {
                    throw new \Exception("更新失败", 1);
                }

            }    

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, "更新失败", 1000);

        }

        return $this->ajaxSuccess($goods_sn_list, 'success');

    }

    /**
     * 中金支付 1402 反扫接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1402(Request $req)
    {
        if(($res = $this->cpcc1402Validate($req))!==true)
        {
            return $res;
        }

        // 提取请求参数
        $institutionID   = $this->institution_id;
        $orderNo         = $req->get("OrderNo");
        $paymentNo       = $req->get("PaymentNo");
        $paymentWay      = $req->get("PaymentWay");
        $paymentScene    = $req->get("PaymentScene");
        $authCode        = $req->get("AuthCode");
        $amount          = intval($req->get("Amount"));
        $expirePeriod    = $req->get("ExpirePeriod");
        $subject         = $req->get("Subject");
        $goodsTag        = $req->get("GoodsTag");
        $remark          = $req->get("Remark");
        $limitPay        = $req->get("LimitPay");
        $notificationURL = $req->get("NotificationURL");

        $ret = $this->generateOrder($institutionID, $orderNo, $paymentNo, $paymentWay, $paymentScene, $amount, $subject);
        if($ret === false)
        {
            $this->ajaxFail(null, 'server order generate failed, pls try again', 1000);
        }

        // 组装 xml
        $xml1402 = config('xmltype.tx1402');
        $simpleXML= new \SimpleXMLElement($xml1402);

        // 赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->OrderNo=$orderNo;
        $simpleXML->Body->PaymentNo=$paymentNo;
        $simpleXML->Body->PaymentWay=$paymentWay;
        $simpleXML->Body->PaymentScene=$paymentScene;
        $simpleXML->Body->AuthCode=$authCode;
        $simpleXML->Body->Amount=$amount;
        $simpleXML->Body->ExpirePeriod=$expirePeriod;
        $simpleXML->Body->Subject=$subject;
        $simpleXML->Body->GoodsTag= $goodsTag;
        $simpleXML->Body->Remark=$remark;
        $simpleXML->Body->LimitPay=$limitPay;
        $simpleXML->Body->NotificationURL=$notificationURL;

        // 签名
        $xmlStr = $simpleXML->asXML();    
        $message=base64_encode(trim($xmlStr));
        $signature=$this->cfcasign_pkcs12(trim($xmlStr));  
        $response=$this->cfcatx_transfer($message,$signature); 
        $plainText=trim(base64_decode($response[0]));

        // 验证签名，返回结果
        $ok=$this->cfcaverify($plainText,$response[1]);
        if($ok!=1)
        {
            // 验证签名失败
            $this->ajaxFail(null, "验签失败", 9999);
        }
        else
        {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="反扫支付";
            $txCode="1402";


            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;
            if($code !=2000)
            {
                return $this->ajaxFail(null, $msg, $code);
            } else {
                $data = [];
                $data['txcode']               = 1402;
                $data['description']          = "反扫支付";
                $data['InstitutionID']        = (string)$simpleXML->Body->InstitutionID;
                $data['OrderNo']              = (string)$simpleXML->Body->OrderNo;
                $data['PaymentNo']            = (string)$simpleXML->Body->PaymentNo;
                $data['PaymentWay']           = (string)$simpleXML->Body->PaymentWay;
                $data['Amount']               = (string)$simpleXML->Body->Amount;
                $data['Status']               = (string)$simpleXML->Body->Status;
                $data['BankNotificationTime'] = (string)$simpleXML->Body->BankNotificationTime;
                $data['CardType']             = (string)$simpleXML->Body->CardType;
                $data['ExpireTime']           = (string)$simpleXML->Body->ExpireTime;
                $data['PayerID']              = (string)$simpleXML->Body->PayerID;
                $data['ResponseCode']         = (string)$simpleXML->Body->ResponseCode;
                $data['ResponseMessage']      = (string)$simpleXML->Body->ResponseMessage;
                $data['Fee']                  = (string)$simpleXML->Body->Fee;
                $data['code']                 =  $code;

                // 判断 status 10 未知  20 成功 30 失败
                if(intval($simpleXML->Body->Status)===false)
                {
                    //return $this->ajaxFail(null, "中金异常 {$simpleXML->Body->Status} {$simpleXML->Body->ResponseMessage}", 2000);
                } else if(intval((string)$simpleXML->Body->Status) == 10){
                    return $this->ajaxFail(null, "中金异常 状态未知", 2001);

                } else if(intval((string)$simpleXML->Body->Status) == 20){
                } else if(intval((string)$simpleXML->Body->Status) == 30){
                    return $this->ajaxFail(null, "中金异常 请求失败 {$simpleXML->Body->Status} {$simpleXML->Body->ResponseMessage} ", 2002);

                } else {
                    return $this->ajaxFail(null, "中金异常 其它", 2003);
                }

                // 订单状态更新
                $serverorderinfo = ServerOrder::where(['order_sn' => $data['PaymentNo']])->first();
                if(is_null($serverorderinfo))
                {
                    // 写入日志， server_order 订单信息写入没有找到
                    Log::info("1402 同步回调 order_no {$data['PaymentNo']}  的 server_order 信息没有找到");
                    
                } else {
                    $serverorderinfo->status = $data['Status'];
                    $saveresult = $serverorderinfo->save();
                    if(false === $saveresult)
                    {
                        // 保存时报，写日志
                        Log::info("1402 同步回调 order_no {$data['PaymentNo']}  的 server_order status {$data['Status']} 状态更细失败");
                        
                    }
                }   
                return $this->ajaxSuccess($data, "success");
            }
        }

        return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    private function cpcc1402Validate(Request $req)
    {
        $institutionID   = $req->get("InstitutionID");
        $orderNo         = $req->get("OrderNo");
        $paymentNo       = $req->get("PaymentNo");
        $paymentWay      = $req->get("PaymentWay");
        $paymentScene    = $req->get("PaymentScene");
        $authCode        = $req->get("AuthCode");
        $amount          = intval($req->get("Amount"));
        $expirePeriod    = $req->get("ExpirePeriod");
        $subject         = $req->get("Subject");
        $goodsTag        = $req->get("GoodsTag");
        $remark          = $req->get("Remark");
        $limitPay        = $req->get("LimitPay");
        $notificationURL = $req->get("NotificationURL");

        //$store_name = $req->get("store_name");

        if(empty($institutionID))
        {
            //return $this->ajaxFail(null," institutionID can not be empty", 1000);
        }

        if(empty($orderNo))
        {
            return $this->ajaxFail(null," orderNo can not be empty", 1001);
        }

        if(empty($paymentNo))
        {
            return $this->ajaxFail(null," paymentNo can not be empty", 1002);
        }

        if(empty($paymentWay))
        {
            return $this->ajaxFail(null," paymentWay can not be empty", 1003);
        }

        
        if(empty($paymentScene))
        {
            return $this->ajaxFail(null," paymentScene can not be empty", 1004);
        }

        if(empty($authCode))
        {
            return $this->ajaxFail(null," authCode can not be empty", 1005);
        }

        if(empty($amount))
        {
            return $this->ajaxFail(null," amount can not be empty", 1006);
        }

        if(empty($subject))
        {
            return $this->ajaxFail(null," subject can not be empty", 1007);
        }

        // if(empty($store_name))
        // {
        //     return $this->ajaxFail(null," store_name can not be empty", 1008);
        // }

        return true;
    }

    /**
     * 中金支付 1811 （分页获取，每页数据不大于10,000条）
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1811(Request $req)
    {
        if(($res = $this->cpcc1811Validate($req))!==true)
        {
            return $res;
        }

        // 提取参数
        $institutionID = $this->institution_id;
        $date          = $req->get("Date");
        $pageno        = $req->get("PageNO");
        $countperpage  = $req->get("CountPerPage");

        $res = $this->fun1811($institutionID, $date, $pageno, $countperpage,false);
        if($res!==true)
        {
            return $res;
        }

        // // 组装 xml
        // $xml1811 = config('xmltype.tx1811');
        // $simpleXML= new \SimpleXMLElement($xml1811);
    
        // // 4.赋值
        // $simpleXML->Head->InstitutionID=$institutionID;
        // $simpleXML->Body->Date=$date;
        // $simpleXML->Body->PageNO=$pageno;
        // $simpleXML->Body->CountPerPage=$countperpage;
        // // 签名
        // $xmlStr = $simpleXML->asXML();    
        // $message=base64_encode(trim($xmlStr));
        // $signature=$this->cfcasign_pkcs12(trim($xmlStr));  
        // $response=$this->cfcatx_transfer($message,$signature); 
        // $plainText=trim(base64_decode($response[0]));

        // // 验证签名，返回结果
        // $ok=$this->cfcaverify($plainText,$response[1]);
        // if($ok!=1)
        // {
        //     // 验证签名失败
        //     $this->ajaxFail(null, "验签失败", 9999);
        // }
        // else
        // {  
        //     $simpleXML= new \SimpleXMLElement($plainText);    
        //     $txName="分页方式下载交易对账单";
        //     $txCode="1811";

        //     $code =(string) $simpleXML->Head->Code;
        //     $msg = (string)$simpleXML->Head->Message;
        //     $total = (string)$simpleXML->Head->TotalCount;


        //     if($code !=2000)
        //     {
        //         return $this->ajaxFail(null, $msg, $code);
        //     } else {
        //         $data = [];
        //         if($total > 1)
        //         {
        //             for($i = 0; $i<$total; $i++)
        //             {
        //                 $tmp = [];
        //                 $tmp['TxType']               = (string)$simpleXML->Body->Tx[$i]->TxType;  
        //                 $tmp['TxSn']                 = (string)$simpleXML->Body->Tx[$i]->TxSn;        
        //                 $tmp['TxAmount']             = (string)$simpleXML->Body->Tx[$i]->TxAmount;        
        //                 $tmp['InstitutionAmount']    = (string)$simpleXML->Body->Tx[$i]->InstitutionAmount;        
        //                 $tmp['PaymentAmount']        = (string)$simpleXML->Body->Tx[$i]->PaymentAmount;        
        //                 $tmp['PayerFee']             = (string)$simpleXML->Body->Tx[$i]->PayerFee;        
        //                 $tmp['Remark']               = (string)$simpleXML->Body->Tx[$i]->Remark;        
        //                 $tmp['BankNotificationTime'] = (string)$simpleXML->Body->Tx[$i]->BankNotificationTime;        
        //                 $tmp['InstitutionFee']       = (string)$simpleXML->Body->Tx[$i]->InstitutionFee;     
        //                 $tmp['SettlementFlag']       = (string)$simpleXML->Body->Tx[$i]->SettlementFlag;        
        //                 $tmp['SplitType']            = (string)$simpleXML->Body->Tx[$i]->SplitType;        
        //                 $tmp['SplitResult']          = (string)$simpleXML->Body->Tx[$i]->SplitResult;        
        //                 $data[] = $tmp;
        //             }

        //         }
        //         return $this->ajaxSuccess($data, "success");
        //     }
        // }

        // return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    private function fun1811($institution, $date, $pageno, $pagecount, $is_json)
    {
        // 组装 xml
        $xml1811 = config('xmltype.tx1811');
        $simpleXML= new \SimpleXMLElement($xml1811);
    
        // 4.赋值
        $simpleXML->Head->InstitutionID=$institution;
        $simpleXML->Body->Date=$date;
        $simpleXML->Body->PageNO=$pageno;
        $simpleXML->Body->CountPerPage=$pagecount;
        // 签名
        $xmlStr = $simpleXML->asXML();    
        $message=base64_encode(trim($xmlStr));
        $signature=$this->cfcasign_pkcs12(trim($xmlStr));  
        $response=$this->cfcatx_transfer($message,$signature, $is_json); 
        $plainText=trim(base64_decode($response[0]));

        // 验证签名，返回结果
        $ok=$this->cfcaverify($plainText,$response[1]);
        if($ok!=1)
        {
            // 验证签名失败
            $this->ajaxFail(null, "验签失败", 9999);
        }
        else
        {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="分页方式下载交易对账单";
            $txCode="1811";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;
            $total = (string)$simpleXML->Head->TotalCount;
            $cur_total = (string)$simpleXML->Body->Tx->count();

            // if($cur_total ==0)
            // {
            //     // 当前查询没有记录，终止 
            //     $this->ajaxFail(null, "query end", 666666);
            // }

            if($code !=2000)
            {
                return $this->ajaxFail(null, $msg, $code);
            } else {
                $ret = [];
                $ret['total'] = $total;
                $data = [];
                if($cur_total >= 1)
                {
                    for($i = 0; $i<$cur_total; $i++)
                    {
                        $tmp                         = [];
                        $tmp['TxType']               = (string)$simpleXML->Body->Tx[$i]->TxType;  
                        $tmp['TxSn']                 = (string)$simpleXML->Body->Tx[$i]->TxSn;        
                        $tmp['TxAmount']             = (string)$simpleXML->Body->Tx[$i]->TxAmount;        
                        $tmp['InstitutionAmount']    = (string)$simpleXML->Body->Tx[$i]->InstitutionAmount;        
                        $tmp['PaymentAmount']        = (string)$simpleXML->Body->Tx[$i]->PaymentAmount;        
                        $tmp['PayerFee']             = (string)$simpleXML->Body->Tx[$i]->PayerFee;        
                        $tmp['Remark']               = (string)$simpleXML->Body->Tx[$i]->Remark;        
                        $tmp['BankNotificationTime'] = (string)$simpleXML->Body->Tx[$i]->BankNotificationTime;  
                        $tmp['MarketOrderNo']        = (string)$simpleXML->Body->Tx[$i]->MarketOrderNo;        

                        $tmp['InstitutionFee']       = (string)$simpleXML->Body->Tx[$i]->InstitutionFee;     
                        $tmp['SettlementFlag']       = (string)$simpleXML->Body->Tx[$i]->SettlementFlag;        
                        $tmp['SplitType']            = (string)$simpleXML->Body->Tx[$i]->SplitType;        
                        $tmp['SplitResult']          = (string)$simpleXML->Body->Tx[$i]->SplitResult;        
                        $data[] = $tmp;
                    }

                }
                $ret['list'] = $data;

                // 做一个特殊处理
                if($is_json)
                {
                    return $ret;
                }

                return $this->ajaxSuccess($ret, "success");
            }
        }

        return true;
        //return $this->ajaxFail(null, 'not implement yet', 1000);
    }


    private function cpcc1811Validate(Request $req)
    {
        $institutionID = $req->get("InstitutionID");
        $date          = $req->get("Date");
        $pageno        = $req->get("PageNO");
        $countperpage  = $req->get("CountPerPage");

        if(empty($institutionID))
        {
            // return $this->ajaxFail(null, "institutionID can not be empty", 1000);
        }

        if(empty($date))
        {
            return $this->ajaxFail(null, "date can not be empty", 1000);
        }

        if(empty($pageno))
        {
            return $this->ajaxFail(null, "pageno can not be empty", 1000);
        }

        if(empty($countperpage))
        {
            return $this->ajaxFail(null, "CountPerPage can not be empty", 1000);
        }

        return true;
    }

    /**
     * 中金支付结算接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1341(Request $req)
    {
        if(($res = $this->cpcc1341Validate($req))!==true)
        {
            return $res;
        }

        // 提取参数
        $institutionID        = $this->institution_id;
        $serialNumber         = $req->get("SerialNumber");
        $orderNo              = $req->get("OrderNo");
        $amount               = intval($req->get("Amount"));
        $remark               = $req->get("Remark");
        $accountType          = intval($req->get("AccountType"));
        $paymentAccountName   = $req->get("PaymentAccountName");
        $paymentAccountNumber = $req->get("PaymentAccountNumber");
        $bankID               = $req->get("BankID");
        $accountName          = $req->get("AccountName");
        $accountNumber        = $req->get("AccountNumber");
        $branchName           = $req->get("BranchName");
        $province             = $req->get("Province");
        $city                 = $req->get("City");

        // 组装 xml
        $xml1341 = config('xmltype.tx1341');
        $simpleXML= new \SimpleXMLElement($xml1341);

        // 4.赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->SerialNumber=$serialNumber;
        $simpleXML->Body->OrderNo=$orderNo;
        $simpleXML->Body->Amount=$amount;
        $simpleXML->Body->Remark=$remark;
        $simpleXML->Body->AccountType=$accountType;
        $simpleXML->Body->PaymentAccountName=$paymentAccountName;
        $simpleXML->Body->PaymentAccountNumber=$paymentAccountNumber;
        $simpleXML->Body->BankAccount->BankID=$bankID;
        $simpleXML->Body->BankAccount->AccountName=$accountName;
        $simpleXML->Body->BankAccount->AccountNumber=$accountNumber;
        $simpleXML->Body->BankAccount->BranchName=$branchName;
        $simpleXML->Body->BankAccount->Province=$province;
        $simpleXML->Body->BankAccount->City=$city;


        $xmlStr = $simpleXML->asXML();    
        $message=base64_encode(trim($xmlStr));
        $signature=$this->cfcasign_pkcs12(trim($xmlStr));  
        $response=$this->cfcatx_transfer($message,$signature); 
        $plainText=trim(base64_decode($response[0]));

        // 验证签名，返回结果
        $ok=$this->cfcaverify($plainText,$response[1]);
        if($ok!=1)
        {
            // 验证签名失败
            $this->ajaxFail(null, "验签失败", 9999);
        }
        else
        {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="市场订单结算";
            $txCode="1341";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;

            $flowinfo = OutflowLog::where(['SerialNumber'=>$serialNumber])->first();
            if(is_null($flowinfo))
            {
                    // 没有找到对应的 outflow 记录
                    Log::info("1341 同步回调 SerialNumber {$serialNumber}  的 outflowinfo 信息没有找到");
                    
            }

            if($code !=2000)
            {
                return $this->ajaxFail(null, $msg, $code);
            } else {
                $data = [];
                $data['txname'] = $txCode;
                $data['desc'] = $txName;



            
                if(!is_null($flowinfo))
                {
                    $flowinfo->status = 1;
                    $saveresult = $flowinfo->save();
                    if(false === $saveresult)
                    {
                        // 记录日志，保存失败
                        Log::info("1341 同步回调 SerialNumber {$serialNumber}  2000 状态信息更新失败");
                        
                    }
                }

                Log::info("1341 同步成功");
                return $this->ajaxSuccess($data, "success");
            }
        }

        return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    private function cpcc1341Validate(Request $req)
    {
        $institutionID        = $req->get("InstitutionID");
        $serialNumber         = $req->get("SerialNumber");
        $orderNo              = $req->get("OrderNo");
        $amount               = intval($req->get("Amount"));
        $remark               = $req->get("Remark");
        $accountType          = intval($req->get("AccountType"));
        $paymentAccountName   = $req->get("PaymentAccountName");
        $paymentAccountNumber = $req->get("PaymentAccountNumber");
        $bankID               = $req->get("BankID");
        $accountName          = $req->get("AccountName");
        $accountNumber        = $req->get("AccountNumber");
        $branchName           = $req->get("BranchName");
        $province             = $req->get("Province");
        $city                 = $req->get("City");

        if(empty($institutionID))
        {
            //return $this->ajaxFail(null," institutionID can not be empty", 1000);
        }

        if(empty($serialNumber))
        {
            return $this->ajaxFail(null," serialNumber can not be empty", 1000);
        }

        if(empty($orderNo))
        {
            return $this->ajaxFail(null," orderNo can not be empty", 1000);
        }

        if(empty($amount))
        {
            return $this->ajaxFail(null," amount can not be empty", 1000);
        }

        if(empty($accountType))
        {
            return $this->ajaxFail(null," accountType can not be empty", 1000);
        }

        if(empty($bankID))
        {
            return $this->ajaxFail(null," bankID can not be empty", 1000);
        }

        if(empty($accountName))
        {
            return $this->ajaxFail(null," accountName can not be empty", 1000);
        }

        if(empty($accountNumber))
        {
            return $this->ajaxFail(null," accountNumber can not be empty", 1000);
        }

        if(empty($branchName))
        {
            return $this->ajaxFail(null," branchName can not be empty", 1000);
        }

        if(empty($province))
        {
            return $this->ajaxFail(null," province can not be empty", 1000);
        }

        if(empty($city))
        {
            return $this->ajaxFail(null," city can not be empty", 1000);
        }

        return true;
    }

    /**
     * 中金查询订单状态接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1410(Request $req)
    {
        if(($res = $this->cpcc1410Validate($req))!==true)
        {
            return $res;
        }

        // 读取参数
        $institutionID = $req->get("InstitutionID");
        $paymentNo     = $req->get("PaymentNo");

        // 组装xml
        $xml1410 = config('xmltype.tx1410');
        $simpleXML= new \SimpleXMLElement($xml1410);

        // 4.赋值
        $simpleXML->Body->InstitutionID=$institutionID;
        $simpleXML->Body->PaymentNo=$paymentNo;

        $xmlStr = $simpleXML->asXML();    
        $message=base64_encode(trim($xmlStr));
        $signature=$this->cfcasign_pkcs12(trim($xmlStr));  
        $response=$this->cfcatx_transfer($message,$signature); 
        $plainText=trim(base64_decode($response[0]));

        $ok=$this->cfcaverify($plainText,$response[1]);
        if($ok!=1)
        {
            // 验证签名失败
            $this->ajaxFail(null, "验签失败", 9999);
        } else {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="二维码支付订单查询";
            $txCode="1410";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;

            if($code !=2000)
            {
                return $this->ajaxFail(null, $msg, $code);
            } else {
                $data = [];
                $data['txname'] = $txCode;
                $data['desc'] = $txName;

                $data['InstitutionID']        = (string)$simpleXML->Body->InstitutionID;
                $data['PaymentNo']            = (string)$simpleXML->Body->PaymentNo;
                $data['PaymentWay']           = (string)$simpleXML->Body->PaymentWay;
                $data['Status']               = (string)$simpleXML->Body->Status;
                $data['CardType']             = (string)$simpleXML->Body->CardType;
                $data['Amount']               = (string)$simpleXML->Body->Amount;
                $data['RefundAmount']         = (string)$simpleXML->Body->RefundAmount;
                $data['DiscountAmount']       = (string)$simpleXML->Body->DiscountAmount;
                $data['CouponAmount']         = (string)$simpleXML->Body->CouponAmount;
                $data['BankNotificationTime'] = (string)$simpleXML->Body->BankNotificationTime;
                $data['Subject']              = (string)$simpleXML->Body->Subject;
                $data['PayerID']              = (string)$simpleXML->Body->PayerID;
                $data['OperatorID']           = (string)$simpleXML->Body->OperatorID;
                $data['StoreID']              = (string)$simpleXML->Body->StoreID;
                $data['TerminalID']           = (string)$simpleXML->Body->TerminalID;
                $data['Remark']               = (string)$simpleXML->Body->Remark;
                $data['ResponseCode']         = (string)$simpleXML->Body->ResponseCode;
                $data['ResponseMessage']      = (string)$simpleXML->Body->ResponseMessage;
                $data['Fee']                  = (string)$simpleXML->Body->Fee;

                
                return $this->ajaxSuccess($data, "success");
            }


            require("response.php");
        }
        
    }

    public function cpcc1410Validate(Request $req)
    {
        $institutionID = $req->get("InstitutionID");
        $paymentNo     = $req->get("PaymentNo");

        if(empty($institutionID))
        {
            // return $this->ajaxFail(null, "institutionID can not be empty ", 1000);
        }

        if(empty($paymentNo))
        {
            return $this->ajaxFail(null, "paymentNo can not be empty ", 1001);
        }

        return true; 
    }

    /**
     * 
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpcNotify(Request $req)
    {
        $this->ccpc1408($req);
    }

    /**
     * 中金回调
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function ccpc1408(Request $req)
    {
        $message = $req->get('message');
        $signature = $req->get('signature');

        // 组装 xml
        $xmlnotify = config('xmltype.txnotify');
        $responseXML= new \SimpleXMLElement($xmlnotify); 

        $plainText=base64_decode($message); 
        $ok=$this->cfcaverify($plainText,$signature);

        if($ok!=1)
        {
            $errInfo="验签失败";    
            $responseXML->Head->Code = "2001";
            $responseXML->Head->Message =$errInfo;
            
        }else{
            $txName = "";
            $simpleXML= new \SimpleXMLElement($plainText);   
            $txCode=$simpleXML->Head->TxCode;
            
            if ($txCode=="1118"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName = "商户订单支付状态变更通知";
            } else if ($txCode=="1119") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "商户订单支付状态变更通知";
            } else if ($txCode=="1138") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "商户订单退款结算状态变更通知";
            } else if ($txCode=="1318") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "市场订单支付状态变更通知";
            } else if ($txCode=="1348") {
                //！！！ 在这里添加商户处理逻辑！！！
                $serial_no      =(string)$simpleXML->Body->SerialNumber;
                $order_no       =(string)$simpleXML->Body->OrderNo;
                $amount         = (string)$simpleXML->Body->Amount;
                $status         = (string)$simpleXML->Body->Status;
                $transafer_time = (string)$simpleXML->Body->TransferTime;
                
                $this->notify1348($serial_no, $order_no, $amount, $status, $transafer_time);

                //以下为演示代码
                $txName =  "市场订单结算状态变更通知";
            } else if ($txCode=="1363") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "市场订单单笔代收结果通知";
            } else if ($txCode=="1363") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "市场订单单笔代收结果通知（短信确认）";
            } else if ($txCode=="1408") {
                //！！！ 在这里添加商户处理逻辑！！！
                $order_no    = (string)$simpleXML->Body->OrderNo;
                $seria_no    = (string)$simpleXML->Body->PaymentNo;
                $status      = (string)$simpleXML->Body->Status;
                $notify_time = (string)$simpleXML->Body->BankNotificationTime;
                $store_id    = (string)$simpleXML->Body->PaymentWay;
                $this->notify1408($order_no, $seria_no, $status, $notify_time, $store_id);
                //以下为演示代码
                $txName =  "市场订单O2O支付状态变更通知";
            } else if ($txCode=="1455") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "市场订单O2O支付资金到账通知";
            } else if ($txCode=="1456") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "市场订单聚合支付结果通知";
            } else if ($txCode=="1712") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "预授权成功结果通知";
            } else if ($txCode=="1722") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "预授权撤销结果通知";
            } else if ($txCode=="1732") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "预授权扣款结果通知";
            } else if ($txCode=="2018") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "实时代扣结果通知";
            } else if ($txCode=="2038") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "实时代扣结果通知";
            } else if ($txCode=="2247") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "商户模式O2O支付退款通知";
            } else if ($txCode=="2248") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "商户模式O2O支付状态变更通知";
            } else if ($txCode=="2249") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "商户模式O2O支付资金到账通知";
            } else if ($txCode=="2353") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "企业账户验证申请结果通知";
            } else if ($txCode=="2702"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "协议签署结果通知";
            } else if ($txCode=="2818") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "支付结果通知";
            } else if ($txCode=="2838") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "退款结果通知";
            } else if ($txCode=="3218"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName = "P2P支付成功通知（托管户）";
            } else if ($txCode=="4233") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户注册成功通知";
            } else if ($txCode=="4243") {
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户银行账户绑定成功通知（托管户）";
            } else if($txCode=="4247"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户银行账户解绑成功通知（托管户）";
            } else if($txCode=="4253"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户充值成功通知（托管户）";
            } else if($txCode=="4257"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户提现成功通知（托管户）";
            } else if($txCode=="4263"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "用户支付账户扣款签约成功通知（托管户）";
            } else if($txCode=="4703"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "电子账户开户通知";
            } else if($txCode=="4723"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "设置电子账户密码状态通知";
            } else if($txCode=="4743"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "电子账户充值通知";
            } else if($txCode=="4753"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "电子账户提现通知";
            } else if($txCode=="4773"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "P2P项目投资支付通知";
            } else if($txCode=="4783"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "P2P项目自动投资签约通知";
            } else if($txCode=="4793"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "P2P徽商债权转让通知";
            } else if($txCode=="6061"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "Pos订单支付结果通知 收银支付/O2O支付/轻支付结果通知";
            } else if($txCode=="6062"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "Pos撤销结果通知";
            } else if($txCode=="6063"){
                //！！！ 在这里添加商户处理逻辑！！！
                //以下为演示代码
                $txName =  "Pos退货结果通知";
            }else {
                $txName = "未知通知类型";
            }       
            $responseXML->Head->Code = "2000";
            $responseXML->Head->Message ="OK.";
        }
        
        // 商户自身逻辑处理完成之后,需要向支付平台返回响应
        $responseXMLStr = $responseXML->asXML();    
        $base64Str = base64_encode(trim($responseXMLStr));
        /**HttpResponse::status(200);
        HttpResponse::setContentType('text/plain');
        HttpResponse::setData($base64Str);
        HttpResponse::send();*/
        print $base64Str;

    }

    /**
     * 同步会员数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncMember(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        // 检查字段
        $fieldsresult = $this->checkFields($data, self::SYNC_MEMBER);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = Member::where(['id'=>$value['id'],'store_code'=>$store_code])->first();
                if(is_null($is_exist))
                {
                    $is_exist = Member::where(['store_code'=>$store_code,'uname'=>$value['uname']])->first();
                }
                if(is_null($is_exist))
                {
                    $tmpuser                    = new Member;
                    $tmpuser->id                = $value['id'];
                    $tmpuser->uname             = $value['uname'];
                    $tmpuser->phone             = $value['phone'];
                    $tmpuser->store_code        = $store_code;
                    $tmpuser->idcard            = $value['idcard'];
                    $tmpuser->deleted           = $value['deleted'];
                    $tmpuser->birthday          = $value['birthday'];
                    $tmpuser->discount          = $value['discount'];
                    $tmpuser->total_consumption = $value['total_consumption'];
                    $tmpuser->total_order       = $value['total_order'];
                    
                    $tmpuser->points            = $value['points'];
                    $tmpuser->balance           = $value['balance'];
                    $tmpuser->gender            = $value['gender'];
                    $tmpuser->create_time       = $value['create_time'];
                    $tmpuser->comment           = $value['comment'];

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save members record failed", 1);
                    } 
                    $save_count++;   
                } else {
                    $is_exist->id                = $value['id'];
                    $is_exist->uname             = $value['uname'];
                    $is_exist->phone             = $value['phone'];
                    $is_exist->idcard            = $value['idcard'];
                    $is_exist->store_code        = $store_code;
                    $is_exist->deleted           = $value['deleted'];
                    $is_exist->birthday          = $value['birthday'];
                    $is_exist->discount          = $value['discount'];
                    $is_exist->total_consumption = $value['total_consumption'];
                    $is_exist->total_order       = $value['total_order'];

                    $is_exist->points            = $value['points'];
                    $is_exist->balance           = $value['balance'];
                    $is_exist->gender            = $value['gender'];
                    $is_exist->create_time       = $value['create_time'];
                    $is_exist->comment           = $value['comment'];

                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update members record failed", 1);
                    }
                    $update_count++;   
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $save_count + $update_count;
            return $this->ajaxSuccess([], "save members data success,{$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

    }

    /**
     * 同步用户数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncUser(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        // 检查字段
        $fieldsresult = $this->checkFields($data, self::SYNC_USER);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count =0;
            $update_count = 0;
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = User::where(['store_code'=>$store_code,'uname'=>$value['uname']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser                      = new User;
                    $tmpuser->id                  = $value['id'];
                    $tmpuser->uname               = $value['uname'];
                    $tmpuser->password            = $value['password'];
                    $tmpuser->store_code          = $store_code;
                    $tmpuser->rank                = $value['rank'];
                    $tmpuser->deleted             = $value['deleted'];
                    $tmpuser->is_active           = $value['deleted'];
                    $tmpuser->realname            = $value['realname'];
                    $tmpuser->business_licence_no = $value['user_number'];
                    $tmpuser->phone               = $value['phone'];


                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save user record failed", 1);
                    } 
                    $save_count++;   
                } else {
                    $is_exist->id                  = $value['id'];
                    $is_exist->uname               = $value['uname'];
                    $is_exist->password            = $value['password'];
                    $is_exist->rank                = $value['rank'];
                    $is_exist->store_code          = $store_code;
                    $is_exist->deleted             = $value['deleted'];
                    $is_exist->is_active           = $value['deleted'];
                    
                    $is_exist->realname            = $value['realname'];
                    $is_exist->business_licence_no = $value['user_number'];
                    $is_exist->phone               = $value['phone'];


                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update user record failed", 1);
                    }
                    $update_count++;   
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $save_count + $update_count;
            return $this->ajaxSuccess([], "save user data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }
        //  解析参数
        
        return true;
    }

    public function syncGoodsSku(Request $req)
    {
        return $this->ajaxFail(null, "api depricated !");
        return true;
    }

    /**
     * 同步商品数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncGoods(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        // 检查字段
        $fieldsresult = $this->checkFields($data, self::SYNC_GOODS);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = Goods::where(['store_code'=>$store_code,'goods_sn'=>$value['goods_sn'], 'id'=>$value['id']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser                    = new Goods;
                    $tmpuser->id                = $value['id'];
                    $tmpuser->cat_id             = is_null($value['cat_id'])?0:$value['cat_id'];
                    $tmpuser->goods_name        = $value['goods_name'];
                    $tmpuser->goods_sn          = $value['goods_sn'];
                    $tmpuser->cost_price        = $value['cost_price'];
                    $tmpuser->shop_price        = $value['shop_price'];
                    $tmpuser->repertory         = $value['repertory'];
                    $tmpuser->repertory_caution = $value['repertory_caution'];
                    $tmpuser->staleTime         = $value['staleTime'];
                    $tmpuser->is_forsale        = $value['is_forsale'];
                    $tmpuser->sale_time         = $value['sale_time'];
                    $tmpuser->is_short          = $value['is_short'];
                    $tmpuser->short_time        = $value['short_time'];
                    $tmpuser->staleTime         = $value['staleTime'];
                    $tmpuser->check             = $value['check'];
                    $tmpuser->type              = $value['type'];
                    $tmpuser->spec              = $value['spec'];
                    $tmpuser->custom            = $value['custom'];
                    $tmpuser->unit              = $value['unit'];
                    $tmpuser->create_time       = $value['create_time'];
                    $tmpuser->deleted           = $value['deleted'];
                    $tmpuser->store_code        = $store_code;

                    $tmpuser->place_code         = $value['place_code'];

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save goods record failed", 1);
                    }  
                    $save_count++;  
                } else {

                    $is_exist->id                = $value['id'];
                    $is_exist->cat_id              = is_null($value['cat_id'])?0:$value['cat_id'];
                    $is_exist->goods_name        = $value['goods_name'];
                    $is_exist->goods_sn          = $value['goods_sn'];
                    $is_exist->cost_price        = $value['cost_price'];
                    $is_exist->shop_price        = $value['shop_price'];
                    $is_exist->repertory         = $value['repertory'];
                    $is_exist->repertory_caution = $value['repertory_caution'];
                    $is_exist->staleTime         = $value['staleTime'];
                    $is_exist->is_forsale        = $value['is_forsale'];
                    $is_exist->sale_time         = $value['sale_time'];

                    $is_exist->place_code         = $value['place_code'];

                    $is_exist->is_short          = $value['is_short'];
                    $is_exist->short_time        = $value['short_time'];
                    $is_exist->staleTime         = $value['staleTime'];
                    $is_exist->check             = $value['check'];
                    $is_exist->type              = $value['type'];
                    $is_exist->spec              = $value['spec'];
                    $is_exist->custom            = $value['custom'];
                    $is_exist->unit              = $value['unit'];
                    $is_exist->create_time       = $value['create_time'];
                    $is_exist->deleted           = $value['deleted'];
                    $is_exist->store_code        = $store_code;




                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update goods record failed", 1);
                    }
                    $update_count++;
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $save_count + $update_count;
            return $this->ajaxSuccess([], "save goods data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }
        //  解析参数
        
        return true;
    }

    /**
     * 同步订单数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncOrder(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        // 检查字段
        $fieldsresult = $this->checkFields($data, self::SYNC_ORDER);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count=0;
            $update_count = 0;
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = Order::where(['store_code'=>$store_code,'order_sn'=>$value['order_sn']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser                   = new Order;
                    $tmpuser->id               = $value['id'];
                    $tmpuser->order_sn         = $value['order_sn'];
                    $tmpuser->create_time      = $value['create_time'];
                    $tmpuser->status           = $value['status'];
                    $tmpuser->pay_type         = $value['pay_type'];
                    $tmpuser->uid              = $value['uid'];
                    $tmpuser->store_code       = $store_code;
                    $tmpuser->user_name        = $value['user_name'];
                    $tmpuser->mid              = is_null($value['mid'])?0:$value['mid'];
                    $tmpuser->total_price      = $value['total_price'];
                    $tmpuser->discount_rate    = $value['discount_rate'];
                    
                    $tmpuser->discounts_price  = $value['discounts_price'];
                    $tmpuser->discount_code    = $value['discount_code'];
                    $tmpuser->change_price     = $value['change_price'];
                    $tmpuser->refund_uid       = is_null($value['refund_uid'])?0:$value['refund_uid'];

                    $tmpuser->receivable_price = $value['receivable_price'];
                    $tmpuser->practical_price  = $value['practical_price'];
                    $tmpuser->total_num        = $value['total_num'];
                    $tmpuser->refund_time      = $value['refund_time'];
                    $tmpuser->deleted          = $value['deleted'];
                    

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save order record failed", 1);
                    }    
                    $save_count++;
                } else {
                    $is_exist->id               = $value['id'];
                    $is_exist->order_sn         = $value['order_sn'];
                    $is_exist->create_time      = $value['create_time'];
                    $is_exist->status           = $value['status'];
                    $is_exist->pay_type         = $value['pay_type'];
                    $is_exist->uid              = $value['uid'];
                    $is_exist->store_code       = $store_code;
                    $is_exist->user_name        = $value['user_name'];
                    $is_exist->mid              = is_null($value['mid'])?0:$value['mid'];
                    $is_exist->total_price      = $value['total_price'];
                    $is_exist->discount_rate    = $value['discount_rate'];
                    
                    $is_exist->discounts_price  = $value['discounts_price'];
                    $is_exist->discount_code    = $value['discount_code'];
                    $is_exist->change_price     = $value['change_price'];
                    $is_exist->refund_uid       = is_null($value['refund_uid'])?0:$value['refund_uid'];
                    

                    $is_exist->receivable_price = $value['receivable_price'];
                    $is_exist->practical_price  = $value['practical_price'];
                    $is_exist->total_num        = $value['total_num'];
                    $is_exist->refund_time      = $value['refund_time'];
                    $is_exist->deleted          = $value['deleted'];
                    $ret                        = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update order record failed", 1);
                    }
                    $update_count++;
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $save_count + $update_count;
            return $this->ajaxSuccess([], "save orders data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

        return true;
    }

    public function syncOrderGoods(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        // 检查字段
        $fieldsresult = $this->checkFields($data, self::SYNC_ORDERGOODS);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = OrderGoods::where(['store_code'=>$store_code,'goods_sn'=>$value['goods_sn'], 'order_id'=>$value['order_id'],'id'=>$value['id']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser                  = new OrderGoods;
                    $tmpuser->id              = $value['id'];
                    $tmpuser->order_id        = $value['order_id'];
                    $tmpuser->goods_id        = $value['goods_id'];
                    $tmpuser->goods_sn        = $value['goods_sn'];
                    $tmpuser->goods_name      = $value['goods_name'];
                    $tmpuser->goods_num       = $value['goods_num'];
                    $tmpuser->goods_price     = $value['goods_price'];
                    $tmpuser->subtotal_price  = $value['subtotal_price'];
                    $tmpuser->discounts_price = $value['discounts_price'];
                    
                    $tmpuser->discount_code   = $value['discount_code'];
                    $tmpuser->discount        = $value['discount'];

                    $tmpuser->deleted         = $value['deleted'];
                    $tmpuser->store_code      = $store_code;

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save order goods record failed", 1);
                    }
                    $save_count ++;    
                } else {
                    $is_exist->id              = $value['id'];
                    $is_exist->order_id        = $value['order_id'];
                    $is_exist->goods_id        = $value['goods_id'];
                    $is_exist->goods_sn        = $value['goods_sn'];
                    $is_exist->goods_name      = $value['goods_name'];
                    $is_exist->goods_num       = $value['goods_num'];
                    $is_exist->goods_price     = $value['goods_price'];
                    $is_exist->subtotal_price  = $value['subtotal_price'];
                    $is_exist->discounts_price = $value['discounts_price'];
                    
                    $is_exist->discount_code   = $value['discount_code'];
                    $is_exist->discount        = $value['discount'];
                    
                    $is_exist->deleted         = $value['deleted'];
                    $is_exist->store_code      = $store_code;

                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update order goods record failed", 1);
                    }
                    $update_count ++;    
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $save_count + $update_count;
            return $this->ajaxSuccess([], "save order goods data success,  {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

        return true;
    }

    /**
     * 根据请求类型验证字段
     * @param  [type] $data [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function checkFields($data, $type)
    {
        $fields = [];
        switch ($type) {
            case self::SYNC_USER:
                $fields = ['id','uname','password','rank','deleted','realname','user_number','phone'];
                break;
            case self::SYNC_GOODS:
                $fields = ['id','cat_id','goods_sn','cost_price','shop_price','repertory','repertory_caution','staleTime','is_forsale','sale_time','is_short','short_time','sale_time','check','type','spec','custom','unit','create_time','deleted'];
                break;
            case self::SYNC_MEMBER:
                $fields = ['id','uname','phone','idcard','deleted','birthday','discount','total_consumption','total_order','points','balance','gender','create_time','comment'];
                break;
            case self::SYNC_GOODSSKU:
                  // 废弃
                break;
            case self::SYNC_ORDERGOODS:
                $fields = ['id','order_id','goods_id','goods_sn','goods_name','goods_num','goods_price','subtotal_price','discounts_price','discount_code','discount','deleted'];    
                break;
            case self::SYNC_CATEGORY:
                // 无
                break;
            case self::SYNC_SHIFTLOG:
                $fields = ['id','uid','shifts_code','start_time','end_time','total_price','cash_price','deleted'];
                break;
            case self::SYNC_SUPPLIER:
                //$fields = ['id','supplier_name','supplier_principal','phone','address','create_time','last_modified','user_name','uid','deleted'];
                $fields = ['id','supplier_name','create_time','user_name','uid','deleted'];
                break;
            case self::SYNC_INOUTSTOCK:
                // $fields = ['id','supplier_name','s_id','goods_name','goods_sn','gid','repertory','in_out_repertory','type','in_out_price','unit','subtotal','uid','user_name','create_time','last_modified','deleted'];
                $fields = ['id','supplier_name','goods_name','goods_sn','gid','repertory','in_out_repertory','type','in_out_price','unit','subtotal','uid','user_name','create_time','last_modified','deleted'];
                break;
            default:
                # code...
                break;
        }

        // check
        if(is_array($data)&&sizeof($data)>0)
        {
            $test = $data[0];
            foreach ($fields as $key => $value) {
                if(!array_key_exists($value, $test))
                {
                    return $this->ajaxFail(null, "{$value} field is required", $type*1100+$key);
                }
            }
        } else {
            return true;
        }

        return true;
    }

    public function syncShiftLog(Request $req)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        $fieldsresult = $this->checkFields($data, self::SYNC_SHIFTLOG);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;

            // fields check
            
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = ShiftLog::where(['store_code'=>$store_code,'id'=>$value['id']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser              = new ShiftLog;
                    $tmpuser->id          = $value['id'];
                    $tmpuser->uid         = $value['uid'];
                    $tmpuser->shifts_code = $value['shifts_code'];
                    $tmpuser->start_time  = $value['start_time'];
                    $tmpuser->end_time    = $value['end_time'];
                    $tmpuser->total_price = $value['total_price'];
                    $tmpuser->cash_price  = $value['cash_price'];
                    $tmpuser->deleted     = $value['deleted'];

                    $tmpuser->total_order     = $value['total_order'];

                    
                    
                    $tmpuser->store_code  = $store_code;

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save members record failed", 1);
                    }   
                    $save_count++; 
                } else {
                    $is_exist->id          = $value['id'];
                    $is_exist->uid         = $value['uid'];
                    $is_exist->shifts_code = $value['shifts_code'];
                    $is_exist->start_time  = $value['start_time'];
                    $is_exist->end_time    = $value['end_time'];
                    $is_exist->total_price = $value['total_price'];
                    $is_exist->cash_price  = $value['cash_price'];
                    $is_exist->deleted     = $value['deleted'];

                    $is_exist->total_order     = $value['total_order'];

                    $is_exist->store_code  = $store_code;

                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update members record failed", 1);
                    }
                    $update_count++; 
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $update_count + $save_count;
            return $this->ajaxSuccess([], "save shiftlog  data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

        return true;

        return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    /**
     * 同步供应商数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncSupplier(Request $req, $storeinfo)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        $fieldsresult = $this->checkFields($data, self::SYNC_SUPPLIER);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;

            // fields check
            
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = Supplier::where(['store_code'=>$store_code,'id'=>$value['id']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser              = new Supplier;
                    $tmpuser->store_code  = $store_code;
                    $tmpuser->store_id  = $storeinfo[0]->local_id;

                    $tmpuser->id                 = $value['id'];
                    $tmpuser->supplier_name      = $value['supplier_name'];
                    $tmpuser->supplier_principal = $value['supplier_principal'];
                    $tmpuser->phone              = $value['phone'];
                    $tmpuser->address            = $value['address'];
                    $tmpuser->create_time        = $value['create_time'];
                    $tmpuser->last_modified      = $value['last_modified'];
                    $tmpuser->user_name          = $value['user_name'];
                    $tmpuser->uid                = $value['uid'];
                    $tmpuser->deleted            = $value['deleted'];

                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save supplier record failed", 1);
                    }   
                    $save_count++; 
                } else {
                    $is_exist->store_code  = $store_code;
                    $is_exist->store_id  = $storeinfo[0]->local_id;

                    $is_exist->id                 = $value['id'];
                    $is_exist->supplier_name      = $value['supplier_name'];
                    $is_exist->supplier_principal = $value['supplier_principal'];
                    $is_exist->phone              = $value['phone'];
                    $is_exist->address            = $value['address'];
                    $is_exist->create_time        = $value['create_time'];
                    $is_exist->last_modified      = $value['last_modified'];
                    $is_exist->user_name          = $value['user_name'];
                    $is_exist->uid                = $value['uid'];
                    $is_exist->deleted            = $value['deleted'];

                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update supplier record failed", 1);
                    }
                    $update_count++; 
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $update_count + $save_count;
            return $this->ajaxSuccess([], "save supplier  data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

        return true;

        return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    /**
     * 同步出入库
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncInOutStock(Request $req, $storeinfo)
    {
        $data = $req->get('data');
        $store_code = $req->get('store_code');

        $fieldsresult = $this->checkFields($data, self::SYNC_SUPPLIER);
        if($fieldsresult !== true)
        {
            return $fieldsresult;
        }

        DB::beginTransaction();
        try {
            $save_count = 0;
            $update_count = 0;

            // fields check
            
            foreach ($data as $key => $value) {
                // 去重
                $is_exist = InOutStockLog::where(['store_code'=>$store_code,'id'=>$value['id']])->first();
                if(is_null($is_exist))
                {
                    $tmpuser              = new InOutStockLog;
                    $tmpuser->store_code  = $store_code;
                    $tmpuser->store_id  = $storeinfo[0]->local_id;

                    $tmpuser->id               = $value['id'];
                    $tmpuser->supplier_name    = $value['supplier_name'];
                    $tmpuser->sid             = $value['sid'];
                    $tmpuser->goods_name       = $value['goods_name'];
                    $tmpuser->goods_sn         = $value['goods_sn'];
                    $tmpuser->gid              = $value['gid'];
                    $tmpuser->repertory        = $value['repertory'];
                    $tmpuser->in_out_repertory = $value['in_out_repertory'];
                    $tmpuser->type             = $value['type'];
                    $tmpuser->in_out_price     = $value['in_out_price'];
                    $tmpuser->unit             = $value['unit'];
                    $tmpuser->subtotal         = $value['subtotal'];
                    $tmpuser->uid              = $value['uid'];
                    $tmpuser->user_name        = $value['user_name'];
                    $tmpuser->create_time      = $value['create_time'];
                    $tmpuser->last_modified    = $value['last_modified'];
                    $tmpuser->deleted          = $value['deleted'];


                    $ret = $tmpuser->save();
                    if($ret === false)
                    {
                        throw new \Exception(" save in_out_stock record failed", 1);
                    }   
                    $save_count++; 
                } else {
                    $is_exist->store_code  = $store_code;
                    $is_exist->store_id  = $storeinfo[0]->local_id;

                    $is_exist->id               = $value['id'];
                    $is_exist->supplier_name    = $value['supplier_name'];
                    $is_exist->sid             = $value['sid'];
                    $is_exist->goods_name       = $value['goods_name'];
                    $is_exist->goods_sn         = $value['goods_sn'];
                    $is_exist->gid              = $value['gid'];
                    $is_exist->repertory        = $value['repertory'];
                    $is_exist->in_out_repertory = $value['in_out_repertory'];
                    $is_exist->type             = $value['type'];
                    $is_exist->in_out_price     = $value['in_out_price'];
                    $is_exist->unit             = $value['unit'];
                    $is_exist->subtotal         = $value['subtotal'];
                    $is_exist->uid              = $value['uid'];
                    $is_exist->user_name        = $value['user_name'];
                    $is_exist->create_time      = $value['create_time'];
                    $is_exist->last_modified    = $value['last_modified'];
                    $is_exist->deleted          = $value['deleted'];

                    $ret = $is_exist->save();
                    if($ret === false)
                    {
                        throw new \Exception(" update in_out_stock record failed", 1);
                    }
                    $update_count++; 
                }

            }
            DB::commit();
            $size = sizeof($data);
            $total = $update_count + $save_count;
            return $this->ajaxSuccess([], "save in_out_stock  data success, {$total} processed , {$save_count} records saved,{$update_count} updated, {$size} input ");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->ajaxFail(null, $e->getMessage(), 1000);
            
        }

        return true;

        return $this->ajaxFail(null, 'not implement yet', 1000);
    }

    /**
     * 同步数据
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function syncData(Request $req)
    {

        
        $store_code = $req->get('store_code');
        $type = $req->get('type');
        $data = $req->get('data');

        if(empty($store_code))
        {
            return $this->ajaxFail(null, 'store_code can not be empty', 1000);
        }

        if(empty($type))
        {
            return $this->ajaxFail(null, 'type can not be empty, 1 user, 2 member, 3 goods_sku 4 goods, 5 order 6 order goods, 7 shift log', 1001);
        }

        if(intval($type) === false)
        {
            return $this->ajaxFail(null, 'type variable type illegal', 1002);   
        }


        if( intval($type)<1 || intval($type)>self::SYNC_INOUTSTOCK)
        {
            return $this->ajaxFail(null, 'type value type illegal', 1003);        
        }

        // store_code 不存在
        $is_exist = User::where(['store_code'=>$store_code, 'deleted'=>0])->get();
        if(empty($is_exist))
        {
            return $this->ajaxFail(null, 'store not found', 1004);   
        }

        $ret = false;
        switch (intval($type)) {
            case self::SYNC_USER:
                $ret =  $this->syncUser($req);
                break;
            case self::SYNC_MEMBER:
                $ret =  $this->syncMember($req);
                break;
            case self::SYNC_GOODSSKU:
                $ret =  $this->syncGoodsSku($req);
                break;
            case self::SYNC_GOODS:
                $ret =  $this->syncGoods($req);
                break;
            case self::SYNC_ORDER:
                $ret = $this->syncOrder($req);
                break;
            case self::SYNC_ORDERGOODS:
                $ret =  $this->syncOrderGoods($req);
                break;
            case self::SYNC_SHIFTLOG:
                $ret = $this->syncShiftLog($req);
                break;
            case self::SYNC_SUPPLIER:
                $ret = $this->syncSupplier($req, $is_exist);
                break;
            case self::SYNC_INOUTSTOCK:
                $ret = $this->syncInOutStock($req, $is_exist);
                break;
            default:
                # code...
                break;
        }

        if($ret !== true)
        {
            return $ret;
        }

        return $this->ajaxSuccess([], 'success');
    }

    /**
     * 获取 goods_sku 详情
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function getSkuGoods(Request $req)
    {
        $barcode = $req->get('barcode');

        if(empty($barcode))
        {
            return $this->ajaxFail(null, "barcode can not be empty", 1000);
        }

        $info = GoodsSku::where(['goods_sn'=>$barcode])->first();
        return $this->ajaxSuccess($info, "success");


    }

    /**
     * 定时任务接口，定时从中金拉取账单数据，生成 prepayment 表
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function pullBillFromCcpc(Request $req)
    {
        $date = $req->get('date');

        /*
        
        if(empty($date))
        {
            return $this->ajaxFail(null, "date can not be empty", 1000);
        }
        */
        if(empty($date))
        {
            // 如果没有指定时间，默认去昨天的数据交易数据
            // 和前天的结算数据
            $date = date('Y-m-d', strtotime("-1 day"));
        }


        //遍历数据，至 error_code = 666666
        $page = 1;

        while(1)
        {
            $response = $this->fun1811($this->institution_id, $date, $page, 10000, true);
            $current_no = sizeof($response['list']);
            
            $total = $response['total'];
            // 如果 current_no 小于 1 ，中断执行
            // if($current_no < 1)
            // {
            //     break;
            // }


            // 写入数据   
            // 开启事务

            DB::beginTransaction();
            try{
                foreach ($response['list'] as $key => $value) {
                    // 去重
                    $is_exist = CpccTxLog::where(['TxSn'=>$value['TxSn']])->first();
                    if($is_exist !== null)
                    {
                        // go to next
                        $is_exist->TxType               =$value['TxType'];
                        $is_exist->TxSn                 =$value['TxSn'];
                        $is_exist->TxAmount             =$value['TxAmount'];
                        $is_exist->InstitutionAmount    =$value['InstitutionAmount'];
                        $is_exist->PaymentAmount        =$value['PaymentAmount'];
                        $is_exist->PayerFee             =$value['PayerFee'];
                        $is_exist->BankNotificationTime =$value['BankNotificationTime'];
                        $is_exist->MarketOrderNo        =$value['MarketOrderNo'];
                        $is_exist->check_date           =$date;
                        $is_exist->InstitutionFee       =$value['InstitutionFee'];
                        $is_exist->SplitType            =$value['SplitType'];
                        $save_result = $is_exist->save();
                        if($save_result === false)
                        {
                            throw new \Exception("save error", 1);
                        } else {

                        }

                    } else {
                        $newrec = new CpccTxLog();
                        $newrec->TxType               =$value['TxType'];
                        $newrec->TxSn                 =$value['TxSn'];
                        $newrec->TxAmount             =$value['TxAmount'];
                        $newrec->InstitutionAmount    =$value['InstitutionAmount'];
                        $newrec->PaymentAmount        =$value['PaymentAmount'];
                        $newrec->PayerFee             =$value['PayerFee'];
                        $newrec->BankNotificationTime =$value['BankNotificationTime'];
                        $newrec->MarketOrderNo        =$value['MarketOrderNo'];
                        $newrec->check_date           =$date;
                        $newrec->InstitutionFee       =$value['InstitutionFee'];
                        $newrec->SplitType            =$value['SplitType'];
                        $save_result = $newrec->save();
                        if($save_result === false)
                        {
                            throw new \Exception("save error", 1);
                        } else {

                        }
                    }



                }

                DB::commit();
                // 写日志
                $log = new GeneralLog();
                $log->message = "拉取成功 参数日期 {$date}";
                $log->date = date('Y-m-d H:i:s', time());
                $log->save();
                // todo 
                
                // 生成昨天的如今 prepayment 
                $this->generatePrepayment($date);

                // 生成对账单数据前天的
                //$this->generateAfterPayment(date("Y-m-d", strtotime($date)-86400));
                $this->generateAfterPayment($date);

                return $this->ajaxSuccess([], "read success, {$total} records date {$date}");
            } catch (Exception $e) {
                DB::rollBack();
                // 写日志
                return $this->ajaxFail(null, "read data fail", 1000);
            }
                     
        }
        
        return $response;

        // 写入数据库
    }

    /**
     * 生成预处理宝
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function generatePrepayment($date)
    {

        $qrytime = strtotime($date);

        // 获取日期时间内的订单
        $timestamp = strtotime($date);
        $draw_date = date('Y-m-d 0:0:0', $timestamp);
        $midnight_date = date("y-m-d 23:59:59", $timestamp);

        $draw_ts = strtotime($draw_date);
        $midnight_ts = strtotime($midnight_date);

        // status = 20 成功订单，创建时间限定
        $orderlist = DB::select("select * from pos_server_order where create_time < {$midnight_ts} and create_time > {$draw_ts} and status =20");
        $loglist = DB::select("select * from pos_cpcc_tx_log where TxType=1402 and check_date='{$date}' ");

        $order_sn       = array_column($orderlist, 'order_sn');
        $txsn           = array_column($loglist, 'TxSn');

        $merge          = array_merge($order_sn, $txsn);
        // 根据差集判断, log_diff 为 log 独有 order_diff 为 order 独有
        $order_diff_log = array_diff($merge, $txsn);
        $log_diff_order = array_diff($merge, $order_sn);

        // exit("total sizeof ".sizeof($merge).'  order size '.sizeof($order_diff_log).' log size '.sizeof($log_diff_order));

        if(sizeof($merge) == sizeof($order_sn))
        {
            // 没有差异
        }


        $order_num = 0;
        $order_total = 0.0;
        $log_num = 0;
        $log_total = 0.0;
        $delta = 0;


        // 计算双方公有的记录 
        $ll = DB::select("SELECT
            o.order_no, o.amount as o_amount,o.notify_time as o_notify_time, o.create_time as o_create_time,o.order_sn as o_serial_no,o.id as o_id,
            l.TxAmount as l_amount, l.BankNotificationTime as l_notify_time,l.TxSn as l_serial_no,l.TxType as l_type,l.id as l_id
                FROM pos_server_order as o
            JOIN pos_cpcc_tx_log as l
            ON o.order_sn = l.TxSn
            where l.TxType=1402 and l.check_date='{$date}'
           
        ");

        // 开启事务
        DB::beginTransaction();
        try{
                foreach ($ll as $key => $value) {
                    // 去重
                    $is_exist = Prepayment::where(['serial_no'=>$value->l_serial_no])->first();

                    // 统计金额数量
                    $order_num +=1;
                    $log_num +=1;
                    $order_total += $value->o_amount;
                    $log_total += $value->l_amount;

                    // 查询店铺信息
                    $usrinfo = User::where(['store_code'=>$value->order_no, 'rank'=>0])->first();
                    $store_name = "";
                    if(is_null($usrinfo))
                    {
                        $store_name = "";
                    }else {
                        $store_name = $usrinfo->store_name;
                    }

                    if($is_exist !==null)
                    {
                        $is_exist->check_date = $date;
                        $is_exist->serial_no = $value->o_serial_no;
                        $is_exist->store_name = $store_name;
                        $is_exist->store_code = $value->order_no;
                        $is_exist->cpcc_amount = $value->l_amount;
                        $is_exist->order_amount = $value->o_amount;

                        if(abs($value->l_amount - $value->o_amount) > 1)
                        {
                            $is_exist->result_status = self::CHECK_NUMBERNOTMATCH;
                        } else {
                            $is_exist->result_status = self::CHECK_SUCCESS;
                        }
                        // $is_exist->status = 0;
                        $is_exist->order_time = $value->o_create_time;
                        $is_exist->cpcc_time = $value->l_notify_time;
                        $is_exist->cpcc_tx_log_id = $value->l_id;
                        $is_exist->order_id = $value->o_id;
                        $saveresult = $is_exist->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }
                    } else {
                        $tmppre = new Prepayment();
                        $tmppre->check_date = $date;
                        $tmppre->serial_no = $value->o_serial_no;
                        $tmppre->store_name = $store_name;
                        $tmppre->store_code = $value->order_no;
                        $tmppre->cpcc_amount = $value->l_amount;
                        $tmppre->order_amount = $value->o_amount;

                        if(abs($value->l_amount - $value->o_amount) > 1)
                        {
                            $tmppre->result_status = self::CHECK_NUMBERNOTMATCH;
                        } else {
                            $tmppre->result_status = self::CHECK_SUCCESS;
                        }
                        $tmppre->status = 0;
                        $tmppre->order_time = $value->o_create_time;
                        $tmppre->cpcc_time = $value->l_notify_time;
                        $tmppre->cpcc_tx_log_id = $value->l_id;
                        $tmppre->order_id = $value->o_id;
                        $saveresult = $tmppre->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }    
                    }
                    


                }
 

                // 差异性插入
                if(sizeof($order_diff_log) > 0)
                {


                    foreach ($order_diff_log as $key => $value) {
                        // 去重
                        
                        $is_exist = Prepayment::where(['serial_no'=>$value])->first();

                        $orderinfo = ServerOrder::where(['order_sn'=>$value])->first();

                        // 统计 order 信息
                        $order_num +=1;
                        $order_total += $orderinfo->amount;

                        if($is_exist !==null)
                        {
                            // 如果存在，不做任何处理
                            $is_exist->check_date = $date;
                            $is_exist->serial_no = $orderinfo->order_sn;
                            // $is_exist->store_name = "orderoly";
                            // $is_exist->store_code = $orderinfo->order_no;
                            $is_exist->cpcc_amount = 0;
                            $is_exist->order_amount = $orderinfo->amount;
                            $is_exist->result_status = self::check_CCPCNOT;// 
                            // $is_exist->status = 0;
                            $is_exist->order_time = $orderinfo->create_time;
                            $is_exist->cpcc_time = 0;
                            $is_exist->cpcc_tx_log_id = 0;
                            $is_exist->order_id = $orderinfo->id;
                            $saveresult = $is_exist->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            } 

                        } else {
                            // 根据id 查找 Order 信息
                            // $orderinfo = ServerOrder::where(['order_sn'=>$value])->first();

                            // // 统计 order 信息
                            // $order_num +=1;
                            // $order_total += $orderinfo->amount;
                            $store = User::where(['store_code' => $orderinfo->order_no, 'rank'=>0])->first();
                            $store_name = "";
                            if(is_null($store))
                            {

                            }else {
                                $store_name = $store->store_name;
                            }

                            unset($tmppre);
                            $tmppre = new Prepayment();
                            $tmppre->check_date = $date;
                            $tmppre->serial_no = $orderinfo->order_sn;
                            $tmppre->store_name = $store_name;
                            $tmppre->store_code = $orderinfo->order_no;
                            $tmppre->cpcc_amount = 0;
                            $tmppre->order_amount = $orderinfo->amount;
                            $tmppre->result_status = self::check_CCPCNOT;// 
                            $tmppre->status = 0;
                            $tmppre->order_time = $orderinfo->create_time;
                            $tmppre->cpcc_time = 0;
                            $tmppre->cpcc_tx_log_id = 0;
                            $tmppre->order_id = $orderinfo->id;
                            $saveresult = $tmppre->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            } 
                        }

                        
                    }                    
                }


                if(sizeof($log_diff_order) > 0)
                {
                    foreach ($log_diff_order as $key => $value) {
                        // 去重
                        $is_exist = Prepayment::where(['serial_no'=>$value])->first();
                        $loginfo = CpccTxLog::where(['TxSn'=>$value])->first();

                        // 统计 log 信息
                        $log_num +=1;
                        $log_total +=$loginfo->TxAmount;

                        if($is_exist !==null)
                        {

                            $is_exist->check_date = $date;
                            $is_exist->serial_no = $loginfo->TxSn;
                            $is_exist->store_name = "-";
                            $is_exist->store_code = "-";
                            $is_exist->cpcc_amount = $loginfo->TxAmount;
                            $is_exist->order_amount = 0;
                            $is_exist->result_status = self::CHECK_ORDERNOT;
                            $is_exist->status = 0;
                            $is_exist->order_time = 0;
                            $is_exist->cpcc_time = $loginfo->BankNotificationTime;
                            $is_exist->cpcc_tx_log_id = $loginfo->id;
                            $is_exist->order_id = 0;
                            $saveresult = $is_exist->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            }  
                        } else {
                            unset($tmppre);
                            $tmppre = new Prepayment();
                            $tmppre->check_date = $date;
                            $tmppre->serial_no = $loginfo->TxSn;
                            $tmppre->store_name = "-";
                            $tmppre->store_code = "-";
                            $tmppre->cpcc_amount = $loginfo->TxAmount;
                            $tmppre->order_amount = 0;
                            $tmppre->result_status = self::CHECK_ORDERNOT;
                            $tmppre->status = 0;
                            $tmppre->order_time = 0;
                            $tmppre->cpcc_time = $loginfo->BankNotificationTime;
                            $tmppre->cpcc_tx_log_id = $loginfo->id;
                            $tmppre->order_id = 0;
                            $saveresult = $tmppre->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            }    
                        }

                        
                    }
                }

            // 生成 order 记录条数， log 记录条数， order 记录金额 log 记录金额 差额 order-log
            $delta = $order_total - $log_total;
            $is_log_exist = AbnormalTransactionLog::where(['check_date' => $date,'tx_type'=>1402])->first();
            if(!is_null($is_log_exist))
            {
                $is_log_exist->log_num = $log_num;
                $is_log_exist->log_total = $log_total;
                $is_log_exist->order_num = $order_num;
                $is_log_exist->order_total = $order_total;
                $is_log_exist->amount = $order_total - $log_total;

                $is_log_exist->save();

            } else {
                $tmp_log = new AbnormalTransactionLog();
                $tmp_log->check_date = $date;
                $tmp_log->tx_type = 1402;
                $tmp_log->log_num = $log_num;
                $tmp_log->log_total = $log_total;
                $tmp_log->order_num = $order_num;
                $tmp_log->amount = $order_total - $log_total;
                $tmp_log->order_total = $order_total;
                $tmp_log->create_time = time();
                $tmp_log->save();
            }

            DB::commit();
            // 写日志
            $log = new GeneralLog();
            $log->message = "prepayment 生成成功 日期 {$date} 总数 ".sizeof($merge).' order 独有 '.sizeof($order_diff_log).' log 独有 '.sizeof($log_diff_order)." oder total {$order_total} log total {$log_total} ";
            $log->date = date('Y-m-d H:i:s', time());
            $log->save();

            return $this->ajaxSuccess([], "read success");
        } catch (Exception $e) {
            DB::rollBack();
            // 写日志
            return $this->ajaxFail(null, "read data fail", 1000);
        }
        
    }


    public function pullRange(Request $req)
    {
        $start = $req->get('start');
        $end = $req->get('end');

        $starttimestamp = strtotime($start);
        $endtimestamp = strtotime($end)+100;
        for ($i=$starttimestamp; $i < $endtimestamp; $i+=86400) { 
            $checkdata['date'] = date("Y-m-d", $i);
            $this->get_web_content(json_encode($checkdata), "http://pos1.123.com/api/apipos/pullbillfromccpc?date=".date('Y-m-d', $i));
        }
    }

    /**
     * 生成对账单数据
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    // 
    public function generateAfterPayment($date)
    {
        $qrytime = strtotime($date);

        // 获取日期时间内的订单
        $timestamp = strtotime($date);
        $draw_date = date('Y-m-d 0:0:0', $timestamp);
        $midnight_date = date("y-m-d 23:59:59", $timestamp);

        $draw_ts = strtotime($draw_date);
        $midnight_ts = strtotime($midnight_date);

        // out_date 划出日期
        $orderlist = DB::select("select * from pos_outflow_log where  out_date='{$date}' and status=1 ");
        $loglist = DB::select("select * from pos_cpcc_tx_log where TxType=1341 and check_date='{$date}' ");


        $order_sn       = array_column($orderlist, 'SerialNumber');
        $txsn           = array_column($loglist, 'TxSn');
        $merge          = array_merge($order_sn, $txsn);
        $order_diff_log = array_diff($merge, $txsn);
        $log_diff_order = array_diff($merge, $order_sn);

        if(sizeof($merge) == sizeof($order_sn))
        {
            // 没有差异
        }


        $order_num = 0;
        $order_total = 0.0;
        $log_num = 0;
        $log_total = 0.0;
        $delta = 0;

        // 取检查日期内的公有记录
        // 可能会出现 serial_no 对上，但是日期对不上的情况，这种情况再对账上是不允许的，因此2个表都要有check_date 过滤
        $ll = DB::select("SELECT
            o.OrderNo as order_no, o.Amount as o_amount,o.notify_time as o_notify_time, o.create_time as o_create_time,o.SerialNumber as o_serial_no,o.id as o_id,
            l.TxAmount as l_amount, l.BankNotificationTime as l_notify_time,l.TxSn as l_serial_no,l.TxType as l_type,l.id as l_id
                FROM pos_outflow_log as o
            JOIN pos_cpcc_tx_log as l
            ON o.SerialNumber = l.TxSn
            where l.TxType=1341 and l.check_date='{$date}' and o.status=1 and o.out_date='{$date}'
           
        ");


        $comonsize = sizeof($ll);
        $odersize = sizeof($order_diff_log);
        $logsize = sizeof($log_diff_order);
        $order_sn_str = json_encode($order_sn);
        $tx_sn_str = json_encode($txsn);
        Log::info("generate after payment  common record {$comonsize}  order only {$odersize}  log only {$logsize}  order {$order_sn_str}  txsn = {$tx_sn_str}");

        // 开启事务
        DB::beginTransaction();
        try{
                foreach ($ll as $key => $value) {
                    // 去重
                    $is_exist = Postpayment::where(['serial_no'=>$value->l_serial_no])->first();

                    // 统计金额数量
                    $order_num +=1;
                    $log_num +=1;
                    $order_total += $value->o_amount;
                    $log_total += $value->l_amount;

                    if($is_exist !==null)
                    {
                        $is_exist->check_date = $date;
                        $is_exist->serial_no = $value->o_serial_no;

                        $store_info = User::where(['store_code'=>$value->order_no, 'rank'=>0])->first();


                        $is_exist->store_name = is_null($store_info)?"-":$store_info->store_name;
                        $is_exist->store_code = $value->order_no;
                        $is_exist->cpcc_amount = $value->l_amount;
                        $is_exist->order_amount = $value->o_amount;



                        if(abs($value->l_amount - $value->o_amount) > 1)
                        {
                            $is_exist->result_status = self::CHECK_NUMBERNOTMATCH;
                        } else {
                            $is_exist->result_status = self::CHECK_SUCCESS;
                        }
                        // $is_exist->status = 0;
                        $is_exist->order_time = $value->o_create_time;
                        $is_exist->cpcc_time = $value->l_notify_time;
                        $is_exist->cpcc_tx_log_id = $value->l_id;
                        $is_exist->order_id = $value->o_id;
                        $saveresult = $is_exist->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }
                    } else {
                        $tmppre = new  Postpayment();
                        $tmppre->check_date = $date;
                        $tmppre->serial_no = $value->o_serial_no;

                        $store_info = User::where(['store_code'=>$value->order_no, 'rank'=>0])->first();

                        $tmppre->store_name = is_null($store_info)?"-":$store_info->store_name;
                        $tmppre->store_code = $value->order_no;
                        $tmppre->cpcc_amount = $value->l_amount;
                        $tmppre->order_amount = $value->o_amount;

                        if(abs($value->l_amount - $value->o_amount) > 1)
                        {
                            $tmppre->result_status = self::CHECK_NUMBERNOTMATCH;
                        } else {
                            $tmppre->result_status = self::CHECK_SUCCESS;
                        }
                        $tmppre->status = 0;
                        $tmppre->order_time = $value->o_create_time;
                        $tmppre->cpcc_time = $value->l_notify_time;
                        $tmppre->cpcc_tx_log_id = $value->l_id;
                        $tmppre->order_id = $value->o_id;
                        $saveresult = $tmppre->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }    
                    }
                    


                }
 


                // 差异性插入
                if(sizeof($order_diff_log) > 0)
                {

                    foreach ($order_diff_log as $key => $value) {
                        // 去重
                        
                        $is_exist =  Postpayment::where(['serial_no'=>$value])->first();

                        // 根据id 查找 Order 信息
                        $orderinfo = OutflowLog::where(['SerialNumber'=>$value])->first();

                        $order_num +=1;
                        $order_total += $orderinfo->amount;

                        if($is_exist !==null)
                        {
                            // 统计 order 信息
                            // $order_num +=1;
                            // $order_total += $orderinfo->amount;
                            continue;
                        }



                        // 统计 order 信息
                        // $order_num +=1;
                        // $order_total += $orderinfo->amount;

                        unset($tmppre);
                        $tmppre = new Postpayment();
                        $tmppre->check_date = $date;
                        $tmppre->serial_no = $orderinfo->SerialNumber;

                        //$store_info = User::where(['store_code'=>$value, 'rank'=>0])->first();


                        //$tmppre->store_name = is_null($store_info)?"no store":$store_info->store_name;
                        $tmppre->store_name = "-";
                        $tmppre->store_code = $orderinfo->OrderNo;
                        $tmppre->cpcc_amount = 0;
                        $tmppre->order_amount = $orderinfo->Amount;
                        $tmppre->result_status = self::check_CCPCNOT;// 
                        $tmppre->status = 0;
                        $tmppre->order_time = $orderinfo->create_time;
                        $tmppre->cpcc_time = 0;
                        $tmppre->cpcc_tx_log_id = 0;
                        $tmppre->order_id = $orderinfo->id;
                        $saveresult = $tmppre->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }                        
                    }                    
                }

                if(sizeof($log_diff_order) > 0)
                {
                    
                    foreach ($log_diff_order as $key => $value) {
                        // 去重
                        $is_exist = Postpayment::where(['serial_no'=>$value])->first();
                        $loginfo = CpccTxLog::where(['TxSn'=>$value])->first();


                        // 统计 log 信息
                        $log_num +=1;
                        $log_total +=$loginfo->TxAmount;

                        if($is_exist !==null)
                        {

                            $is_exist->check_date = $date;
                            $is_exist->serial_no = $loginfo->TxSn;

                            // 1341 没有market order _no
                            //$store_info = User::where(['store_code'=>$loginfo->MarketOrderNo, 'rank'=>0])->first();

                            $outflowinfo = OutflowLog::where(['SerialNumber'=>$value])->first();
                            $store_name = "-";
                            $store_code = "-";
                            if(!is_null($outflowinfo))
                            {
                                $store_code = $outflowinfo->OrderNo;
                                $store_in = User::where(['rank'=>0, 'store_code'=>$store_code])->first();
                                if(!is_null($store_in))
                                {
                                    $store_name = $store_in->store_name;
                                }
                            } 

                        
                            $is_exist->store_name = $store_name;

                            $is_exist->store_code = $store_code;
                            $is_exist->cpcc_amount = $loginfo->TxAmount;
                            $is_exist->order_amount = 0;
                            $is_exist->result_status = self::CHECK_ORDERNOT;
                            // $is_exist->status = 0;
                            $is_exist->order_time = 0;
                            $is_exist->cpcc_time = empty($loginfo->BankNotificationTime)?time():$loginfo->BankNotificationTime;
                            $is_exist->cpcc_tx_log_id = $loginfo->id;
                            $is_exist->order_id = 0;
                            $saveresult = $is_exist->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            }  
                        } else {
                            unset($tmppre);
                            // 根据 serial_no 在 outflow 中找 store_name 和 store_code 
                            $outflowinfo = OutflowLog::where(['SerialNumber'=>$value])->first();
                            $store_name = "-";
                            $store_code = "-";
                            if(!is_null($outflowinfo))
                            {
                                $store_code = $outflowinfo->OrderNo;
                                $store_in = User::where(['rank'=>0, 'store_code'=>$store_code])->first();
                                if(!is_null($store_in))
                                {
                                    $store_name = $store_in->store_name;
                                }
                            } 
                           

                            $tmppre = new Postpayment();
                            $tmppre->check_date = $date;
                            $tmppre->serial_no = $loginfo->TxSn;
                            $tmppre->store_name = $store_name;
                            $tmppre->store_code = $store_code;
                            $tmppre->cpcc_amount = $loginfo->TxAmount;
                            $tmppre->order_amount = 0;
                            $tmppre->result_status = self::CHECK_ORDERNOT;
                            $tmppre->status = 0;
                            $tmppre->order_time = 0;
                            $tmppre->cpcc_time = empty($loginfo->BankNotificationTime)?time():$loginfo->BankNotificationTime;
                            $tmppre->cpcc_tx_log_id = $loginfo->id;
                            $tmppre->order_id = 0;
                            $saveresult = $tmppre->save();
                            if($saveresult === false)
                            {
                                throw new \Exception("Error Processing Request", 1);
                            }    
                        }

                        
                    }
                }

            // 生成 order 记录条数， log 记录条数， order 记录金额 log 记录金额 差额 order-log
            $delta = $order_total - $log_total;
            $is_log_exist = AbnormalTransactionLog::where(['check_date' => $date,'tx_type'=>1341])->first();
            if(!is_null($is_log_exist))
            {
                $is_log_exist->log_num = $log_num;
                $is_log_exist->log_total = $log_total;
                $is_log_exist->order_num = $order_num;
                $is_log_exist->order_total = $order_total;
                $is_log_exist->amount = $order_total - $log_total;

                $is_log_exist->save();

            } else {
                $tmp_log = new AbnormalTransactionLog();
                $tmp_log->check_date = $date;
                $tmp_log->tx_type = 1341;
                $tmp_log->log_num = $log_num;
                $tmp_log->log_total = $log_total;
                $tmp_log->order_num = $order_num;
                $tmp_log->amount = $order_total - $log_total;
                $tmp_log->order_total = $order_total;
                $tmp_log->create_time = time();
                $tmp_log->save();
            }

            DB::commit();
            // 写日志
            $log = new GeneralLog();
            $log->message = "afterpayment 生成成功 日期 {$date} 总数 ".sizeof($merge).' order 独有 '.sizeof($order_diff_log).' log 独有 '.sizeof($log_diff_order);
            $log->date = date('Y-m-d H:i:s', time());
            $log->save();

            return $this->ajaxSuccess([], "read success");
        } catch (Exception $e) {
            DB::rollBack();
            // 写日志
            return $this->ajaxFail(null, "read data fail", 1000);
        }
        
    }


    public function generateOutflowPrepayment(Request $req)
    {
        $date = $req->get('date');
        if(empty($date))
        {
            return $this->ajaxFail(null, "date can not be empty", 1000);
        }


        $orderlist = DB::select("select * from pos_outflow_log where 1");
        $loglist = DB::select("select * from pos_cpcc_tx_log where 1");

        $order_sn       = array_column($orderlist, 'SerialNumber');
        $txsn           = array_column($loglist, 'TxSn');
        $merge          = array_merge($order_sn, $txsn);
        $order_diff_log = array_diff($order_sn, $txsn);
        $log_diff_order = array_diff($txsn, $order_sn);

        if(sizeof($merge) == sizeof($order_sn))
        {
            // 没有差异
        }


        $ll = DB::select("SELECT
            o.OrderNo, o.Amount as o_amount,o.create_time as o_notify_time, o.create_time as o_create_time,o.SerialNumber as o_serial_no,o.id as o_id,
            l.TxAmount as l_amount, l.BankNotificationTime as l_notify_time,l.TxSn as l_serial_no,l.TxType as l_type,l.id as l_id
                FROM pos_outflow_log as o
            JOIN pos_cpcc_tx_log as l
            ON o.order_sn = l.TxSn
            where l.TxType=1341
           
        ");

        // 开启事务
        DB::beginTransaction();
        try{
                foreach ($ll as $key => $value) {
                    // 去重
                    $is_exist = Prepayment::where(['serial_no'=>$value->l_serial_no])->first();
                    if($is_exist !==null)
                    {
                        continue;
                    }
                    // Prepayment::
                    $tmppre = new Prepayment();
                    $tmppre->check_date = "12345";
                    // exit(json_encode($value->o_serial_no));
                    $tmppre->serial_no = $value->o_serial_no;
                    // exit($tmppre->seria_no);
                    $tmppre->store_name = "待处理1";
                    $tmppre->store_code = $value->order_no;
                    $tmppre->cpcc_amount = $value->l_amount;
                    $tmppre->order_amount = $value->o_amount;
                    if(abs($value->l_amount - $value->o_amount) > 1)
                    {
                        $tmppre->result_status = self::CHECK_NUMBERNOTMATCH;
                    } else {
                        $tmppre->result_status = self::CHECK_SUCCESS;
                    }
                    $tmppre->status = 0;
                    $tmppre->order_time = $value->o_create_time;
                    $tmppre->cpcc_time = $value->l_notify_time;
                    $tmppre->cpcc_tx_log_id = $value->l_id;
                    $tmppre->order_id = $value->o_id;
                    $saveresult = $tmppre->save();
                    if($saveresult === false)
                    {
                        throw new \Exception("Error Processing Request", 1);
                    }


                }
 

                // 差异性插入
                if(sizeof($order_diff_log) > 0)
                {

                    foreach ($order_diff_log as $key => $value) {
                        // 去重
                        
                        $is_exist = Prepayment::where(['serial_no'=>$value])->first();
                        if($is_exist !==null)
                        {
                            continue;
                        }

                        $orderinfo = OutflowLog::where(['order_sn'=>$value])->first();

                        $tmppre = new Prepayment();
                        $tmppre->check_date = "12345";
                        $tmppre->serial_no = $orderinfo->SerialNumber;
                        $tmppre->store_name = "待处理";
                        $tmppre->store_code = $orderinfo->OrderNo;
                        $tmppre->cpcc_amount = 0;
                        $tmppre->order_amount = $orderinfo->Amount;
                        $tmppre->result_status = self::check_CCPCNOT;// 
                        $tmppre->status = 0;
                        $tmppre->order_time = $orderinfo->create_time;
                        $tmppre->cpcc_time = 0;
                        $tmppre->cpcc_tx_log_id = 0;
                        $tmppre->order_id = $orderinfo->id;
                        $saveresult = $tmppre->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }                        
                    }                    
                }

                if(sizeof($log_diff_order) > 0)
                {
                    foreach ($log_diff_order as $key => $value) {
                        // 去重
                        $is_exist = Prepayment::where(['serial_no'=>$value])->first();
                        if($is_exist !==null)
                        {
                            continue;
                        }

                        $loginfo = CpccTxLog::where(['TxSn'=>$value])->first();

                        $tmppre = new Prepayment();
                        $tmppre->check_date = "12345";
                        $tmppre->serial_no = $loginfo->TxSn;
                        $tmppre->store_name = "待处理";
                        $tmppre->store_code = "xxxx";
                        $tmppre->cpcc_amount = $loginfo->TxAmount;
                        $tmppre->order_amount = 0;
                        $tmppre->result_status = self::CHECK_ORDERNOT;
                        $tmppre->status = 0;
                        $tmppre->order_time = 0;
                        $tmppre->cpcc_time = $loginfo->BankNotificationTime;
                        $tmppre->cpcc_tx_log_id = $loginfo->id;
                        $tmppre->order_id = 0;
                        $saveresult = $tmppre->save();
                        if($saveresult === false)
                        {
                            throw new \Exception("Error Processing Request", 1);
                        }
                    }
                }

            DB::commit();
            return $this->ajaxSuccess([], "read success");
        } catch (Exception $e) {
            DB::rollBack();
            // 写日志
            return $this->ajaxFail(null, "read data fail", 1000);
        }

    }

    /**
     * 预处理订单生成接口
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function generatePreApi(Request $req)
    {
        $date = $req->get('date');
        if(empty($date))
        {
            return $this->ajaxFail(null, "date can not be empty", 1000);
        }

        $ret = $this->generatePrepayment($date);
        if($ret !== true){
            return $ret;
        }

        // 生成预处理表
        exit('生成预处理表');
    }

    /**
     * 检查安卓更新
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function checkUpdate(Request $req)
    {
        return $this->ajaxFail(null, 'not implemtn yet', 1000);
    }

    /**
     * 1402 接口生成服务器订单
     * @return [type] [description]
     */
    private function generateOrder($institutionID, $orderNo, $paymentNo, $paymentWay, $paymentScene, $amount, $subject)
    {
        $serverorder = new ServerOrder();
        $serverorder->order_no = $orderNo;
        $serverorder->amount = $amount;
        $serverorder->payment_way = $paymentWay;
        $serverorder->order_sn = $paymentNo;
        $serverorder->create_time = time();
        $serverorder->status = 0;
        $result = $serverorder->save();
        return $result;

    }

    /**
     * 反扫1402回调函数
     * @param  [type] $order_no     [description]
     * @param  [type] $seria_no     [description]
     * @param  [type] $status       [description]
     * @param  [type] $notify_timem [description]
     * @param  [type] $store_id     [description]
     * @return [type]               [description]
     */
    private function notify1408($order_no, $serial_no, $status, $notify_time, $store_id)
    {
         Log::info("1402 异步回调 order_no {$order_no}  serial {$serial_no} status {$status} notify {$notify_time} store_id {$store_id}");
         $rec = ServerOrder::where(['order_no'=>$order_no, "order_sn"=>$serial_no])->first();
         if($rec === null)
         {
            $newserverorder = new ServerOrder();
            $newserverorder->order_no = $order_no;
            $newserverorder->order_sn = $serial_no;
            $newserverorder->payment_way = $store_id;
            $newserverorder->notify_time = $notify_time;
            $newserverorder->status = $status;

            $newserverorder->create_time = time();
            $newserverorder->save();
            Log::info("1402 异步回调 order_no {$order_no}  serial {$serial_no} status {$status} notify {$notify_time} store_id {$store_id}  record not found");
            return;
         }

        $rec->status = $status;
        $rec->notify_time = $notify_time;
        $result = $rec->save();
        if($result === false)
        {
            Log::info("1402 异步回调 order_no {$order_no}  serial {$serial_no} status {$status} notify {$notify_time} store_id {$store_id}  update server order failed");
        }
    }

    /**
     * 结算1341划出异步回调
     * @return [type] [description]
     */
    private function notify1348($serial_no, $order_no, $amount, $status, $transafer_time)
    {
        $info = OutflowLog::where(['SerialNumber'=>$serial_no, 'OrderNo'=>$order_no])->first();
        if($info === null)
        {
            Log::info("1341 异步回调 serial_no {$serial_no}  order_no {$order_no} amount {$amount} status {$status} transafer_time {$transafer_time} record not found");
            $newoutflowlog = new OutflowLog();
            $userinfo = User::where(['store_code'=>$order_no, 'rank'=>0])->first();
            if(is_null($userinfo))
            {
                return;
            }

            $newoutflowlog->SerialNumber = $serial_no;
            $newoutflowlog->OrderNo = $order_no;
            $newoutflowlog->Amount = $amount;
            //$newoutflowlog->AccountType = "";
            //$newoutflowlog->PaymentAccountName = "";
            //$newoutflowlog->PaymentAccountNumbe = "";
            $newoutflowlog->BankID = $userinfo->bank_id;
            $newoutflowlog->AccountName = $userinfo->account_name;
            $newoutflowlog->AccountNumber = $userinfo->account_no;
            $newoutflowlog->create_time = time();
            $newoutflowlog->notify_time = time();
            //$newoutflowlog->out_date = date('Y-m-d', time());
            $newoutflowlog->message = "自动生成,ccpc 回调成功";
            $newoutflowlog->status = intval($status)==40?1:$newoutflowlog->status;
            $newoutflowlog->save();
            return;
        }

        //update
        if(abs($info->Amount - $amount)>1)
        {
            Log::info("1341 异步回调 serial_no {$serial_no}  order_no {$order_no} amount {$amount} status {$status} transafer_time {$transafer_time} amount not match {$info->amount} vs ccps {$amount}");    
        }

        $info->notify_time = time();
        //$info->check_date = date('Y-m-d', time());
        $info->message = "自动生成,ccpc 回调成功";
        $info->status = intval($status)==40?1:$newoutflowlog->status;
        $result = $info->save();
        if($result === false)
        {
            Log::info("1341 异步回调 serial_no {$serial_no}  order_no {$order_no} amount {$amount} status {$status} transafer_time {$transafer_time} update failed");
            return;    
        }
        Log::info("1341 异步回调 serial_no {$serial_no}  order_no {$order_no} amount {$amount} status {$status} transafer_time {$transafer_time} complted");
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

    private function cfcatx_transfer($message,$signature, $is_json=false){ 
        $post_data = array();
        $post_data['message'] = $message;
        $post_data['signature'] = $signature;
        $response= $this->get_web_content($this->data_encode($post_data), null, $is_json );
        $response=trim($response);
        
        return explode(",",$response);
    }

    private function get_web_content( $curl_data, $url= null ,$is_json=false)
    {
        if($url == null)
            $url = $this->cpcc_url;

        $options = array(
            CURLOPT_RETURNTRANSFER => true,         // return web page
            CURLOPT_HEADER         => false,        // don't return headers
            CURLOPT_FOLLOWLOCATION => true,         // follow redirects
            CURLOPT_ENCODING       => "",           // handle all encodings
            CURLOPT_USERAGENT      => "institution",     // who am i
            CURLOPT_AUTOREFERER    => true,         // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
            CURLOPT_TIMEOUT        => 120,          // timeout on response
            CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
            CURLOPT_POST            => 1,            // i am sending post data
            CURLOPT_POSTFIELDS     => $curl_data,    // this are my post vars
            CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => false,        //
            CURLOPT_VERBOSE        => 1                //
        );

        $ch      = curl_init($url);
        curl_setopt_array($ch,$options);
        if($is_json)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        } else {
            curl_setopt($ch,CURLOPT_HTTPHEADER,array("Expect:"));
        }
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
        
    } 

    private function cfcasign_pkcs12($plainText){
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $file = dirname(__FILE__).'/pro/ent.pfx';
        } else {
            $file = dirname(__FILE__).'/dev/test.pfx';
        }

        $p12cert = array();
        //$file = dirname(__FILE__).'/test.pfx';
        $fd = fopen($file, 'r');
        $p12buf = fread($fd, filesize($file));
        fclose($fd);

        if($is_product)
        {
            openssl_pkcs12_read($p12buf, $p12cert, 'star12345678');
        } else {
            openssl_pkcs12_read($p12buf, $p12cert, 'cfca1234');
        }

        $pkeyid = $p12cert["pkey"];
        $binary_signature = "";
        openssl_sign($plainText, $binary_signature, $pkeyid,OPENSSL_ALGO_SHA1);
        return bin2hex($binary_signature);

    }

    private function cfcaverify($plainText,$signature){
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $file = dirname(__FILE__).'/pro/payment.cer';
        } else {
            $file = dirname(__FILE__).'/dev/paytest.cer';
        }

        $fcert = fopen($file, "r"); 
        $cert = fread($fcert, 8192); 
        fclose($fcert);         
        $binary_signature = pack("H" . strlen($signature), $signature); 
        $ok = openssl_verify($plainText, $binary_signature, $cert);
        return $ok;
    }

    private function data_encode($data, $keyprefix = "", $keypostfix = "") {
          assert( is_array($data) );
          $vars=null;
          foreach($data as $key=>$value) {
            if(is_array($value)) $vars .= data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
            else $vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
          }
          return $vars;
    }


    /**
     * 中金支持银行列表
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function banklist(Request $req)
    {

        $list = Bank::all();
        return $this->ajaxSuccess($list, 'success');
    }


    public function storelist(Request $req)
    {
        $list = User::where(['rank'=>0, 'is_active'=>1, 'deleted'=>0])->get();
        return $this->ajaxSuccess($list, 'success');   
    }

    public function province(Request $req)
    {
        $prolist = Region::where(['parent_id'=>1])->get();
        return $this->ajaxSuccess($prolist, 'success');
    }

    /**
     * 市列表 需要 省id 查询
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function city(Request $req)
    {
        $id = $req->get('id');
        if(empty($id))
        {
            return $this->ajaxFail(null, "province id can not be empty",1000);
        }

        $city = Region::where(['parent_id'=>$id])->get();
        return $this->ajaxSuccess($city, 'success');
    }        

    /**
     * 区列表，如要 市 id 查询
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function area(Request $req)
    {
        $id = $req->get('id');
        if(empty($id))
        {
            return $this->ajaxFail(null, "city id can not be empty", 1000);
        }

        $city = Region::where(['parent_id'=>$id])->get();
        return $this->ajaxSuccess($city, 'success');
    }

    public function test(Request $req)
    {
        echo "is_product ".json_encode(env('IS_PRODUCT'));
        echo "<br/>";
        echo "url =".$this->cpcc_url;
        echo "<br/>";
        echo "ins_id =".$this->institution_id;

        return;
    }

    public function getOrderStatus(Request $req)
    {
        $store_code = $req->get('store_code');
        $order_sn = $req->get('order_sn');

        if(empty($store_code))
        {
            // 店铺编码不能为空
            return $this->ajaxFail(null, "store_code can not be empty", 1000);
        }

        if(empty($order_sn))
        {
            // 序列号不能为空
            return $this->ajaxFail(null, "order_sn can not be empty", 1001);
            
        }

        $order_info = ServerOrder::where(['order_no'=>$store_code, 'order_sn'=>$order_sn])->first();
        if(is_null($order_info))
        {
            // 订单不存在
            return $this->ajaxFail(null, "order not found", 1001);
        }


        return $this->ajaxSuccess($order_info, 'success');

    }

}
