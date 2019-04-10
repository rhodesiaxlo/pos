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




class ApiPosController extends Controller
{

    private $cpcc_url = "";
    private $cpcc_url_dev = "https://test.cpcn.com.cn/Gateway/InterfaceII";
    private $cpcc_url_pro = "https://www.china-clearing.com/Gateway/InterfaceII";

    const SYNC_USER = 1;
    const SYNC_MEMBER = 2;
    const SYNC_GOODSSKU = 3;
    const SYNC_GOODS = 4;
    const SYNC_ORDER =5;
    const SYNC_ORDERGOODS = 6;
    const SYNC_SHIFTLOG = 7;
    const SYNC_CATEGORY = 8;

    // 支付方式
    // 订单状态
    // 支付场景
    // 卡类型


   public function __construct()
   {
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $this->cpcc_url = $this->cpcc_url_pro;
        } else {
            $this->cpcc_url = $this->cpcc_url_dev;
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

        if(intval($result['rank'])!==0)
        {
            return $this->ajaxFail([], 'account illegal', 1006);
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
            return $this->ajaxFail([], "store_code can not be empty", 1000);
        }

        if(empty($type))
        {
            return $this->ajaxFail([], "type can not be empty", 1002);
        }

        if(intval($type)===false || intval($type)> self::SYNC_CATEGORY || intval($type) < self::SYNC_USER)
        {
            return $this->ajaxFail([], "type value illegal", 1003);
        }

        // 需要同步的信息
        // 用户信息
        $where['store_code'] = $store_code;
        $userinfo =User::where($where)->get();

        if(empty($userinfo[0]))
        {
            return $this->ajaxFail([], "store not found", 1001);   
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
        if(($res = $this->cpcc1402Validate($req))!==true)
        {
            return $res;
        }

        // 提取请求参数
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
            $this->ajaxFail([], "验签失败", 9999);
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
                return $this->ajaxFail([], $msg, $code);
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
                return $this->ajaxSuccess($data, "success");
            }
        }


        return $this->ajaxFail([], 'not implement yet', 1000);
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

        if(empty($institutionID))
        {
            return $this->ajaxFail([]," institutionID can not be empty", 1000);
        }

        if(empty($orderNo))
        {
            return $this->ajaxFail([]," orderNo can not be empty", 1001);
        }

        if(empty($paymentNo))
        {
            return $this->ajaxFail([]," paymentNo can not be empty", 1002);
        }

        if(empty($paymentWay))
        {
            return $this->ajaxFail([]," paymentWay can not be empty", 1003);
        }

        
        if(empty($paymentScene))
        {
            return $this->ajaxFail([]," paymentScene can not be empty", 1004);
        }

        if(empty($authCode))
        {
            return $this->ajaxFail([]," authCode can not be empty", 1005);
        }

        if(empty($amount))
        {
            return $this->ajaxFail([]," amount can not be empty", 1006);
        }

        if(empty($subject))
        {
            return $this->ajaxFail([]," subject can not be empty", 1007);
        }

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
        $institutionID = $req->get("InstitutionID");
        $date          = $req->get("Date");
        $pageno        = $req->get("PageNO");
        $countperpage  = $req->get("CountPerPage");

        // 组装 xml
        $xml1811 = config('xmltype.tx1811');
        $simpleXML= new \SimpleXMLElement($xml1811);
    
        // 4.赋值
        $simpleXML->Head->InstitutionID=$institutionID;
        $simpleXML->Body->Date=$date;
        $simpleXML->Body->PageNO=$pageno;
        $simpleXML->Body->CountPerPage=$countperpage;
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
            $this->ajaxFail([], "验签失败", 9999);
        }
        else
        {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="分页方式下载交易对账单";
            $txCode="1811";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;
            $total = (string)$simpleXML->Head->TotalCount;


            if($code !=2000)
            {
                return $this->ajaxFail([], $msg, $code);
            } else {
                $data = [];
                if($total > 1)
                {
                    for($i = 0; $i<$total; $i++)
                    {
                        $tmp = [];
                        $tmp['TxType']               = (string)$simpleXML->Body->Tx[$i]->TxType;  
                        $tmp['TxSn']                 = (string)$simpleXML->Body->Tx[$i]->TxSn;        
                        $tmp['TxAmount']             = (string)$simpleXML->Body->Tx[$i]->TxAmount;        
                        $tmp['InstitutionAmount']    = (string)$simpleXML->Body->Tx[$i]->InstitutionAmount;        
                        $tmp['PaymentAmount']        = (string)$simpleXML->Body->Tx[$i]->PaymentAmount;        
                        $tmp['PayerFee']             = (string)$simpleXML->Body->Tx[$i]->PayerFee;        
                        $tmp['Remark']               = (string)$simpleXML->Body->Tx[$i]->Remark;        
                        $tmp['BankNotificationTime'] = (string)$simpleXML->Body->Tx[$i]->BankNotificationTime;        
                        $tmp['InstitutionFee']       = (string)$simpleXML->Body->Tx[$i]->InstitutionFee;     
                        $tmp['SettlementFlag']       = (string)$simpleXML->Body->Tx[$i]->SettlementFlag;        
                        $tmp['SplitType']            = (string)$simpleXML->Body->Tx[$i]->SplitType;        
                        $tmp['SplitResult']          = (string)$simpleXML->Body->Tx[$i]->SplitResult;        
                        $data[] = $tmp;
                    }

                }
                return $this->ajaxSuccess($data, "success");
            }
        }

        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    private function cpcc1811Validate(Request $req)
    {
        $institutionID = $req->get("InstitutionID");
        $date          = $req->get("Date");
        $pageno        = $req->get("PageNO");
        $countperpage  = $req->get("CountPerPage");

        if(empty($institutionID))
        {
            return $this->ajaxFail([], "institutionID can not be empty", 1000);
        }

        if(empty($date))
        {
            return $this->ajaxFail([], "date can not be empty", 1000);
        }

        if(empty($pageno))
        {
            return $this->ajaxFail([], "pageno can not be empty", 1000);
        }

        if(empty($countperpage))
        {
            return $this->ajaxFail([], "CountPerPage can not be empty", 1000);
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
            $this->ajaxFail([], "验签失败", 9999);
        }
        else
        {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="市场订单结算";
            $txCode="1341";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;


            if($code !=2000)
            {
                return $this->ajaxFail([], $msg, $code);
            } else {
                $data = [];
                $data['txname'] = $txCode;
                $data['desc'] = $txName;
                
                return $this->ajaxSuccess($data, "success");
            }
        }

        return $this->ajaxFail([], 'not implement yet', 1000);
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
            return $this->ajaxFail([]," institutionID can not be empty", 1000);
        }

        if(empty($serialNumber))
        {
            return $this->ajaxFail([]," serialNumber can not be empty", 1000);
        }

        if(empty($orderNo))
        {
            return $this->ajaxFail([]," orderNo can not be empty", 1000);
        }

        if(empty($amount))
        {
            return $this->ajaxFail([]," amount can not be empty", 1000);
        }

        if(empty($accountType))
        {
            return $this->ajaxFail([]," accountType can not be empty", 1000);
        }

        if(empty($bankID))
        {
            return $this->ajaxFail([]," bankID can not be empty", 1000);
        }

        if(empty($accountName))
        {
            return $this->ajaxFail([]," accountName can not be empty", 1000);
        }

        if(empty($accountNumber))
        {
            return $this->ajaxFail([]," accountNumber can not be empty", 1000);
        }

        if(empty($branchName))
        {
            return $this->ajaxFail([]," branchName can not be empty", 1000);
        }

        if(empty($province))
        {
            return $this->ajaxFail([]," province can not be empty", 1000);
        }

        if(empty($city))
        {
            return $this->ajaxFail([]," city can not be empty", 1000);
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
            $this->ajaxFail([], "验签失败", 9999);
        } else {  
            $simpleXML= new \SimpleXMLElement($plainText);    
            $txName="二维码支付订单查询";
            $txCode="1410";

            $code =(string) $simpleXML->Head->Code;
            $msg = (string)$simpleXML->Head->Message;

            if($code !=2000)
            {
                return $this->ajaxFail([], $msg, $code);
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
            return $this->ajaxFail([], "institutionID can not be empty ");
        }

        if(empty($paymentNo))
        {
            return $this->ajaxFail([], "paymentNo can not be empty ");
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
            case slef::SYNC_USER:
                return $this->syncUser($req);
                break;
            case slef::SYNC_MEMBER:
                return $this->syncMember($req);
                break;
            case slef::SYNC_GOODSSKU:
                return $this->syncGoodsSku($req);
                break;
            case slef::SYNC_GOODS:
                return $this->syncGoods($req);
                break;
            case slef::SYNC_ORDER:
                return $this->syncOrder($req);
                break;
            case slef::SYNC_ORDERGOODS:
                return $this->syncOrderGoods($req);
                break;
            case slef::SYNC_SHIFTLOG:
                return $this->syncShiftLog($req);
                break;
            default:
                # code...
                break;
        }

        return $this->ajaxFail([], 'not implement yet', 1000);
    }

    /**
     * 定时任务接口，定时从中金拉取账单数据，生成 prepayment 表
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function pullBillFromCcpc(Request $req)
    {

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

    private function cfcatx_transfer($message,$signature){ 
        $post_data = array();
        $post_data['message'] = $message;
        $post_data['signature'] = $signature;
        
        $response= $this->get_web_content($this->data_encode($post_data) );
        $response=trim($response);
        
        return explode(",",$response);
    }

    private function get_web_content( $curl_data )
    {
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

        $ch      = curl_init($this->cpcc_url);
        curl_setopt_array($ch,$options);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Expect:"));
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
        
    } 

    private function cfcasign_pkcs12($plainText){
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $file = dirname(__FILE__).'/pro/test.pfx';
        } else {
            $file = dirname(__FILE__).'/dev/test.pfx';
        }

        $p12cert = array();
        //$file = dirname(__FILE__).'/test.pfx';
        $fd = fopen($file, 'r');
        $p12buf = fread($fd, filesize($file));
        fclose($fd);
        openssl_pkcs12_read($p12buf, $p12cert, 'cfca1234');
        
        $pkeyid = $p12cert["pkey"];
        $binary_signature = "";
        openssl_sign($plainText, $binary_signature, $pkeyid,OPENSSL_ALGO_SHA1);
        return bin2hex($binary_signature);

    }

    private function cfcaverify($plainText,$signature){
        $is_product = intval(env('IS_PRODUCT', 0));

        if($is_product)
        {
            $file = dirname(__FILE__).'/pro/paytest.cer';
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
            return $this->ajaxFail([], "province id can not be empty",1000);
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
            return $this->ajaxFail([], "city id can not be empty", 1000);
        }

        $city = Region::where(['parent_id'=>$id])->get();
        return $this->ajaxSuccess($city, 'success');
    }

}
