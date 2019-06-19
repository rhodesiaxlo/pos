<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    protected $table = 'pos_permissions';
    public $timestamps = false;
    protected $primaryKey = "id";
}
