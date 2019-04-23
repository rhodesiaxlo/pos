<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use  App\Models\StoreIncr;

class OrderIncr extends Model
{
    protected $table = "pos_order_incr";
    public $timestamps = false;

    public static function getId()
    {

    }
}
