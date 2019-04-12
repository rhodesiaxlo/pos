<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Prepayment extends Model
{
    protected $table = "pos_prepayment";
    public $timestamps = false;

    /**
     * 和 pos_order 表 1 对 多关系
     * @return [type] [description]
     */
    public function orders()
    {
    	return $this->belongsTo('App\Models\Pos\Order', 'order_id');
    }

    /**
     * 中金交易记录 1 对 1
     * @return [type] [description]
     */
    public function cpccTxs()
    {
    	return $this->belongsTo('App\Models\Pos\cpccTxs', 'cpcc_tx_log_id');
    }

    public function abnormalLog()
    {

    }

    public function outflowLog()
    {
    	
    }


}
