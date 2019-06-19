<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'pos_user_role';
    public $timestamps = false;
    protected $primaryKey = "id";
}
