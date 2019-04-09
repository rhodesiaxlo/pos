<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "pos_category";

    public function goodsSkus()
    {
    	$this->hasMany('App\Models\GoodsSkus', 'cat_id');
    }
}
