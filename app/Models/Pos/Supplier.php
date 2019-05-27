<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'pos_supplier';
    public $timestamps = false;
    protected $primaryKey = "local_id";
}
