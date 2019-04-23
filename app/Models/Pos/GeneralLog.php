<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class GeneralLog extends Model
{
    protected $table = "pos_general_log";
    public $timestamps = false;

    public function write_log()
    {
    	
    }
}
