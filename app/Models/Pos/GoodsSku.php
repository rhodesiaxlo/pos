<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class GoodsSku extends Model
{
    protected $table = "pos_goods_sku";
    public $timestamps = false;
    /**
     * 和 pos_category 多对一关系
     * @return [type] [description]
     */
    public function category()
    {
    	return $this->belongsTo('App\Models\Pos\Category', 'cat_id');
    }

}
