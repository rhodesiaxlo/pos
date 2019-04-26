<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\AbnormalTransactionLog;
use App\Models\Pos\Prepayment;
use App\Models\Pos\Postpayment;

use App\Models\Pos\OutflowLog;
use App\Models\Pos\OutflowPrepayment;
use App\Models\Pos\User;
use App\Models\Pos\Order;

use DB;
use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;
use  App\Models\Admin\AdminUser;
use Illuminate\Support\Facades\Log;

/**
 * 收银交易控制器
 */
class WriteOffController extends Controller
{
    /**
     * 结算页面
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function index(Request $req)
    {
        // 根据日期选择 prepayment , prepayment 里面有 log_id
        $date = date('Y-m-d',strtotime("-1 day"));
        $getdate = $req->get('date');
        if(!empty($getdate))
            $date = $getdate;

        //$date = "2019-04-10";
        if($req->isMethod('POST'))
        {
            $tmpdate = $req->get('date');
            if(empty($tmpdate))
            {
                // 报错
            }
            $date = $tmpdate;
        }
        $time = strtotime($date);
        $search['date'] = $date;
        // 生成凌晨和午夜的时间段
        $drawn = date('Y-m-d 0:0:0', $time);
        $midnight = date('Y-m-d 23:59:59', $time);

        $drawntimestamp = strtotime($drawn);
        $midnighttimestamp = strtotime($midnight);

        $outflows = OutflowLog::where(['check_date'=>$date])->get();
        return view('pos.tx.payment')->with('outflows', $outflows)->with('search', $search);
    }

    /**
     * 批量结算
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function outflow(Request $req)
    {

        if($req->isMethod('POST'))
        {
            $id_list = $req->get('checked');
            if(empty($id_list) || is_array($id_list))
            {
                // 报错
            }
            $ids = json_decode($id_list, true);
            foreach ($ids as $key => $value) {
                // 分开结算
                // curl 请求
                $tmpoutflow = OutflowLog::find($value);
                if(is_null($tmpoutflow))
                {
                    // 报错
                    continue;
                }

                // 判断结算状态
                if($tmpoutflow->status == 1)
                {
                    // 已结算成功，无需结算
                    //continue;
                }

                // 组装参数，发送报文
                $postdata = [];
                $postdata['InstitutionID']        = '004792';
                $postdata['SerialNumber']         = $tmpoutflow->SerialNumber;
                $postdata['OrderNo']              = $tmpoutflow->OrderNo;
                $postdata['Amount']               = $tmpoutflow->Amount;
                $postdata['Remark']               = "remark";
                $postdata['AccountType']          = 11;
                $postdata['PaymentAccountName']   = "";
                $postdata['PaymentAccountNumber'] = "";
                $postdata['BankID']               = $tmpoutflow->BankID;
                $postdata['AccountName']          = $tmpoutflow->AccountName;
                $postdata['AccountNumber']        = $tmpoutflow->AccountNumber;
                $postdata['BranchName']           = "whatever";
                $postdata['Province']             = "whatever";
                $postdata['City']                 = "whatever";
                $ret = $this->get_web_content($postdata, $_SERVER['SERVER_NAME']."/api/apipos/ccpc1341", true);
                $json_str = json_decode($ret, true);
                if($json_str['code'] == 0)
                {
                    // 出错，记录日志
                    Log::info("结算出错， 出错信息 {$json_str['message']}");
                    $tmpoutflow->message = $json_str['message'];
                    $tmpoutflow->save();
                    exit(json_encode(['code'=>1,'message'=>"结算出错， 出错信息 {$json_str['message']}"]));
                }else{
                    $tmpoutflow->status = 1;
                    $saveresult = $tmpoutflow->save();
                    if($saveresult === false)
                    {
                        // 报错
                        exit(json_encode(['code'=>1,'message'=>'更新outflow 订单失败']));
                        
                    }
                }
                

            }

            exit(json_encode(['code'=>1,'message'=>'success']));
            
        }
        exit("批量结算");
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
}
