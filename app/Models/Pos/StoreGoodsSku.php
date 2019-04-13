<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品 sku 商户基础信息表
 */
class StoreGoodsSku extends Model
{
    protected $table = "pos_store_goods_sku";
    protected $primaryKey = "local_id";
    public $timestamps = false;


    public function category()
    {
    	return $this->belongsTo('App\Models\Pos\Category', 'cat_id');
    }

    /**
     * 和 goods 1 对 多关系
     * @return [type] [description]
     */
    public function goods()
    {
    	return $this->hasMany('App\Models\Pos\Goods');
    }

}
