<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

/**
 * 可变商品信息表，便利店商品详细信息
 */
class Goods extends Model
{
    protected $table = "pos_goods";
    protected $primaryKey = "local_id";

    /**
     * 和 store_goods_sku 多 对 1 关系
     * @return [type] [description]
     */
    public function goodsSku()
    {
    	return $this->belongsTo('App\Models\Pos\StoreGoodsSku', 'skuid', 'id');
    }

    public function user()
    {
    	return $this->belongsTo('App\Models\Pos\User', 'user_id');
    }
}
