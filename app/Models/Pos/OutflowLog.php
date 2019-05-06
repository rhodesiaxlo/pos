<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class OutflowLog extends Model
{
    protected $table = "pos_outflow_log";
    public $timestamps = false;

    public function bank()
    {
    	return $this->belongsTo('App\Models\Pos\Bank','BankID');
    }
}
