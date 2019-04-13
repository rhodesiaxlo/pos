<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class ShiftLog extends Model
{
    protected $table = "pos_shift_log";
    protected $primaryKey = "local_id";
    public $timestamps = false;

    public function cashier()
    {
    	$this->belongsTo('App\Models\Pos\User', 'uid');
    }

}
