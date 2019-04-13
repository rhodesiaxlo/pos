<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\AbnormalTransactionLog;
use App\Models\Pos\Prepayment;
use App\Models\Pos\OutflowLog;
use App\Models\Pos\OutflowPrepayment;
use DB;
use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;
use  App\Models\Admin\AdminUser;

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
        
        $prepayments = DB::table('pos_prepayment')->whereBetween('pos_prepayment.order_time',array($drawntimestamp, $midnighttimestamp))->get();

        $prepayments = [];
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


        $outflows = OutflowLog::where(['check_date'=>$date])->get();
    	return view('pos.tx.payment')->with('outflows', $outflows);
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

            if($id==0)
            {
                // 没有记录，创建新记录
                $tmp = new AbnormalTransactionLog();
                $tmp->amount = $amount;
                $tmp->check_date = $check_date;
                $tmp->tx_type = $tx_type;
                $tmp->message = $message;
                $tmp->admin_id= $operator;
                $tmp->admin_name = $adminuser->name;
                $tmp->create_time = time();
                $tmp->status = 1;

                $tmp->save();
            } else {
                $tmpinfo = AbnormalTransactionLog::where(['id'=>$id])->first();
                $tmpinfo->amount = $amount;
                $tmpinfo->check_date = $check_date;
                $tmpinfo->tx_type = $tx_type;
                $tmpinfo->message = $message;
                $tmpinfo->admin_id= $operator;
                $tmpinfo->admin_name = $adminuser->name;
                $tmpinfo->create_time = time();
                $tmpinfo->status =2;

                $tmpinfo->save();
            }
            exit(json_encode(['code'=>1,'message'=>'success']));
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

            if($id==0)
            {
                // 没有记录，创建新记录
                $tmp = new AbnormalTransactionLog();
                $tmp->amount = $amount;
                $tmp->check_date = $check_date;
                $tmp->tx_type = $tx_type;
                $tmp->message = $message;
                $tmp->confirm_id= $operator;
                $tmp->confirm_name = $adminuser->name;
                $tmp->status = 2;

                $tmp->save();
            } else {
                $tmpinfo = AbnormalTransactionLog::where(['id'=>$id])->first();
                $tmpinfo->amount = $amount;
                $tmpinfo->check_date = $check_date;
                $tmpinfo->tx_type = $tx_type;
                $tmpinfo->message = $message;
                $tmpinfo->confirm_id= $operator;
                $tmpinfo->confirm_name = $adminuser->name;
                $tmpinfo->status = 2;
                $tmpinfo->save();
            }
            exit(json_encode(['code'=>1,'message'=>'success']));
        }
        return view('pos.tx.dlgfirstcheck');
        exit("初审");
    }
}
