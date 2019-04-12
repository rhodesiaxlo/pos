<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Pos\AbnormalTransactionLog;
use App\Models\Pos\Prepayment;
use App\Models\Pos\OutflowLog;



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
        $logs = AbnormalTransactionLog::all();
        $prepayments = Prepayment::all();
        exit(json_encode($logs));
    	return view('pos.tx.depositconfirm')->with('logs', $logs)->with('prepayments', $prepayments);
    }

    /**
     * 还款结算
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function payment(Request $req)
    {
        if($req->isMethod('post'))
        {
            exit(json_encode(['code'=>1,'message'=>'success']));
        }
        $outflows = OutflowLog::all();
    	return view('pos.tx.payment')->with('outflows', $outflows);
    }

    /**
     * 结算对账单
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function withdrawConfirm(Request $req)
    {
    	return view('pos.tx.withdrawconfirm');
    }
}
