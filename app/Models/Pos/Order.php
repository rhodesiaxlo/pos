<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pos\OrderIncr;

class Order extends Model
{
    protected $table = "pos_order";
    public $timestamps = false;
    protected $primaryKey = "local_id";

    /**
     * 收银员
     * @return [type] [description]
     */
    public function cashier()
    {
    	return $this->belongsTo('App\Models\Pos\User', 'uid');
    }

    public function member()
    {
    	return $this->belongsTo('App\Models\Pos\Member', 'mid');
    }

    public function refunder()
    {
    	return $this->belongsTo('App\Models\Pos\User', 'refund_id');
    }

    /**
     * 多对一关系
     * @return [type] [description]
     */
    public function prepayment()
    {
    	return $this->belongsTo('App\Models\Pos\Prepayment', 'pre_payment_id');
    }

    /**
     * 一对多关系
     * @return [type] [description]
     */
    public function orderGoods()
    {
    	return $this->hasMany('App\Models\Pos\OrderGoods');
    }

    /**
     * 生成随机订单编号
     * @return [type] [description]
     */
    public static function getRandomOrderSn($code=1402)
    {
        // to do 
        $new = new OrderIncr();
        $new->message = time();
        $id = $new->save();

        return $code."_".strval(time()).rand(1000,9999);
    }
}
