<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    protected $table = "pos_order_goods";
    protected $primaryKey = "local_id";
    public $timestamps = false;

    /**
     * pos_order 多 对 1 关系
     */
    public function Order()
    {
    	return $this->belongsTo('App\Models\Pos\Order', 'order_id', 'id');
    }

    /**
     * pos_goods 多 对 1 关系
     * @return [type] [description]
     */
    public function goods()
    {
    	return $this->belongsTo('App\Models\Pos\Goods', 'goods_id', 'id');
    }
}
