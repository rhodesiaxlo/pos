<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class InOutStockLog extends Model
{
    protected $table = 'pos_in_out_stock_log';
    public $timestamps = false;
    protected $primaryKey = "local_id";
}
