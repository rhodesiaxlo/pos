<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = "pos_member";
    protected $primaryKey = "local_id";
    public $timestamps = false;
}
