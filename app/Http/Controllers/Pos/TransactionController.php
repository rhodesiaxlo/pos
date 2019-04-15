<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\AbnormalTransactionLog;
use App\Models\Pos\Prepayment;
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
class TransactionController extends Controller
{
    public function index(Request $req)
    {
        return redirect('/pos/transaction/depositconfirm');
    }

    public function add(Request $req)
    {

    }

    public function edit(Request $req)
    {

    }

    public function del(Request $req)
    {

    }

    /**
     * 存款对账单
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function depositConfirm(Request $req)
    {
        // 根据日期选择 prepayment , prepayment 里面有 log_id
        $date = date('Y-m-d',strtotime("-1 day"));

        $getdate = $req->get('date');
        if(!empty($getdate))
            $date = $getdate;

        //$date = "2019-04-10";
        if($req->isMethod('POST'))
        {
            $tmpdate = $req->get('chatTime');
            if(empty($tmpdate))
            {
                // 报错
            }
            $date = $tmpdate;
        }

        // 
        $time = strtotime($date);
        $search['date'] = $date;
        // 生成凌晨和午夜的时间段
        $drawn = date('Y-m-d 0:0:0', $time);
        $midnight = date('Y-m-d 23:59:59', $time);

        $drawntimestamp = strtotime($drawn);
        $midnighttimestamp = strtotime($midnight);

        //exit(" drawn = {$drawn} midnight = {$midnight}");
        
        //$prepayments = DB::table('pos_prepayment')->whereBetween('pos_prepayment.order_time',array($drawntimestamp, $midnighttimestamp))->get();
        $prepayments = DB::table('pos_prepayment')->where(['check_date'=>$date])->get();


        // $logs = DB::table('pos_abnormal_transaction_log')->whereBetween('pos_abnormal_transaction_log.(month, day, year)',array($drawntimestamp, $midnighttimestamp))->first();
        $logs = DB::table('pos_abnormal_transaction_log')->where(['check_date'=>$date,'tx_type'=>1402])->first();
    	return view('pos.tx.depositconfirm')->with('logs', $logs)->with('prepayments', $prepayments)->with('search', $search);
    }

    /**
     * 还款结算
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function payment(Request $req)
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
     * 结算对账单
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function withdrawConfirm(Request $req)
    {
        // 根据日期选择 prepayment , prepayment 里面有 log_id
        $date = date('Y-m-d',strtotime("-1 day"));
        $getdate = $req->get('date');
        if(!empty($getdate))
            $date = $getdate;
        //$date = "2019-04-10";
        if($req->isMethod('POST'))
        {
            $tmpdate = $req->get('chatTime');
            if(empty($tmpdate))
            {
                // 报错
            }
            $date = $tmpdate;
        }

        // 
        $search['date'] = $date;

        // 
        $time = strtotime($date);
        // 生成凌晨和午夜的时间段
        $drawn = date('Y-m-d 0:0:0', $time);
        $midnight = date('Y-m-d 23:59:59', $time);

        $drawntimestamp = strtotime($drawn);
        $midnighttimestamp = strtotime($midnight);

        //exit(" drawn = {$drawn} midnight = {$midnight}");
        
        $prepayments = DB::table('pos_prepayment')->whereBetween('pos_prepayment.order_time',array($drawntimestamp, $midnighttimestamp))->get();

        $prepayments = [];
        //$logs = DB::table('pos_abnormal_transaction_log')->whereBetween('pos_abnormal_transaction_log.create_time',array($drawntimestamp, $midnighttimestamp))->first();
        $logs = DB::table('pos_abnormal_transaction_log')->where(['check_date'=>$date,'tx_type'=>1343])->first();
        
    	return view('pos.tx.withdrawconfirm')->with("logs", $logs)->with('prepayments', $prepayments)->with('search', $search);
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
                    continue;
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
                $ret = $this->get_web_content($postdata, "http://pos1.123.com/api/apipos/ccpc1341", true);
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

    /**
     * 初审
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function firstCheck(Request $req)
    {
        if($req->isMethod('POST'))
        {

            $loginTokenName = Auth::guard('admin')->getName();
            $operator=Session::get($loginTokenName);
            $adminuser = AdminUser::find($operator);
            $id = $req->get('id');
            $tx_type = $req->get('tx_type');
            $message = $req->get('message');
            $amount = $req->get('amount');
            $check_date = $req->get('date');

            $exist = AbnormalTransactionLog::where(['id'=>$id])->first();
            if(!is_null($exist)&&$exist->status==2)
            {
                exit(json_encode(['code'=>0, 'message'=>'已完成复审']));
            }

            DB::beginTransaction();
            try {
                if($id==0)
                {
                    // 没有记录，创建新记录
                    $tmp = new AbnormalTransactionLog();
                    //$tmp->amount = $amount;
                    $tmp->check_date = $check_date;
                    $tmp->tx_type = $tx_type;
                    $tmp->message = $message;
                    $tmp->admin_id= $operator;
                    $tmp->admin_name = $adminuser->name;
                    $tmp->create_time = time();
                    $tmp->status = 1;

                    $saveresult = $tmp->save();
                    if(false === $saveresult)
                    {
                        throw new \Exception("prepayments 更新失败!", 1);
                        //exit(json_encode(['code'=>0, 'message'=>'保存失败']));
                    }
                } else {
                    $tmpinfo = AbnormalTransactionLog::where(['id'=>$id])->first();
                    //$tmpinfo->amount = $amount;
                    $tmpinfo->check_date = $check_date;
                    $tmpinfo->tx_type = $tx_type;
                    $tmpinfo->message = $message;
                    $tmpinfo->admin_id= $operator;
                    $tmpinfo->admin_name = $adminuser->name;
                    $tmpinfo->create_time = time();
                    $tmpinfo->status =1;

                    $saveresult = $tmpinfo->save();
                    if(false === $saveresult)
                    {
                        throw new \Exception("prepayments 更新失败!", 1);
                        //exit(json_encode(['code'=>0, 'message'=>'保存失败']));
                    }
                }

                $result = Prepayment::where(['check_date'=>$check_date])->update(['status'=>1]);
                if(false === $result)
                {
                    throw new \Exception("prepayments 更新失败!", 1);
                    
                }

                DB::commit();
                exit(json_encode(['code'=>1,'message'=>'success']));
            } catch (Exception $e) {
                DB::rollback();
                exit(json_encode(['code'=>0, 'message'=>$e->getMessage()]));
            }

        }
        return view('pos.tx.dlgfirstcheck');
        exit("初审");

    }

    /**
     * 复审
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function reCheck(Request $req)
    {
        if($req->isMethod('POST'))
        {

            $loginTokenName = Auth::guard('admin')->getName();
            $operator=Session::get($loginTokenName);
            $adminuser = AdminUser::find($operator);
            $id = $req->get('id');
            $tx_type = $req->get('tx_type');
            $message = $req->get('message');
            $amount = $req->get('amount');
            $check_date = $req->get('date');

            $exist = AbnormalTransactionLog::where(['id'=>$id])->first();
            if(!is_null($exist)&&$exist->status==0)
            {
                exit(json_encode(['code'=>0, 'message'=>'请先完成初审']));
            }


            DB::beginTransaction();
            try {
                if($id==0)
                {
                    // 没有记录，创建新记录
                    $tmp = new AbnormalTransactionLog();
                    //$tmp->amount = $amount;
                    $tmp->check_date = $check_date;
                    $tmp->tx_type = $tx_type;
                    $tmp->message = $message;
                    $tmp->confirm_id= $operator;
                    $tmp->confirm_name = $adminuser->name;
                    $tmp->status = 2;

                    $saveresult = $tmp->save();
                    if(false === $saveresult)
                    {
                        throw new \Exception("创建 AbnormalTransactionLog 失败", 1);
                        
                    }

                } else {
                    $tmpinfo = AbnormalTransactionLog::where(['id'=>$id])->first();
                    //$tmpinfo->amount = $amount;
                    $tmpinfo->check_date = $check_date;
                    $tmpinfo->tx_type = $tx_type;
                    $tmpinfo->message = $message;
                    $tmpinfo->confirm_id= $operator;
                    $tmpinfo->confirm_name = $adminuser->name;
                    $tmpinfo->status = 2;
                    $saveresult = $tmpinfo->save();
                    if(false === $saveresult)
                    {
                        throw new \Exception("更新 AbnormalTransactionLog 失败", 1);
                    }
                }

                // 标记prepayments
                $result = Prepayment::where(['check_date'=>$check_date])->update(['status'=>2]);
                if(false === $result)
                {
                    throw new \Exception("prepayments 更新失败!", 1);
                }

                // 生成 outflow 记录，标记 prepaymetn 记录
                // 根据日期和状态生成
                // $rest = Prepayment::where(['check_date'=>$check_date, 'status'=>2, 'result_status'=>0])->select(array(\DB::raw('sum(cpcc_amount) as log_amount'),'store_name','store_code'))->get();

                $rest = Prepayment::where(['check_date'=>$check_date])->get();

                // $rest = DB::select("select `cpcc_amount`, `store_code`, `store_name` from pos_prepayment where check_date={$check_date}");
                $ret = [];
                foreach ($rest as $key => $value) {
                    // 根据store_code 选择
                    if(!isset($ret[$value['store_code']]))
                    {
                        $ret[$value->store_code] = [];
                    }

                    $ret[$value->store_code]['store_name'] = $value->store_name;
                    $ret[$value->store_code]['store_code'] = $value->store_code;
                    if(isset($ret[$value->store_code]['amount']))
                    {
                        $ret[$value->store_code]['amount'] += $value->cpcc_amount;    
                    }else{
                        $ret[$value->store_code]['amount'] = $value->cpcc_amount;
                    }
                    
                }

                // 写入 outflowlog
                foreach ($ret as $key => $value) {
                    $outflow = new OutflowLog();
                    $outflow->OrderNo = $value['store_code'];
                    $outflow->check_date = $check_date;
                    $outflow->create_time = time();
                    $outflow->Amount = $value['amount'];

                    $storeinfo = User::where(['store_code'=>$value['store_code'], 'rank'=>0, 'deleted'=>0])->first();
                    if(is_null($storeinfo))
                    {
                        // 报错
                        throw new   \Exception("找不到店铺信息", 1);
                        
                    }

                    if( intval($storeinfo->bank_id) ===false || intval($storeinfo->bank_id)<100)
                    {
                        // 报错
                        throw new   \Exception("没有设置店铺银行卡信息，无法结算", 1);
                        
                    }

                    $outflow->BankID = $storeinfo->bank_id;
                    $outflow->AccountName = $storeinfo->account_name;
                    $outflow->AccountNumber = $storeinfo->account_no;
                    $out_id = $outflow->save();

                    if($out_id === false)
                    {
                        // 报错
                        throw new   \Exception("outflow 保存失败", 1);
                    }

                }

                DB::commit();
                
                exit(json_encode(['code'=>1,'message'=>'success']));
            } catch (Exception $e) {
                DB::rollback();
                exit(json_encode(['code'=>0, 'message'=>$e->getMessage()]));
            }


        }
        return view('pos.tx.dlgfirstcheck');
        exit("初审");
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
