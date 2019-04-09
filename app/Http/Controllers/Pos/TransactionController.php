<?php

namespace App\Http\Controllers\Pos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    	return view('pos.tx.depositconfirm');
    }

    /**
     * 还款结算
     * @param  Request $req [description]
     * @return [type]       [description]
     */
    public function payment(Request $req)
    {
    	return view('pos.tx.payment');
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
