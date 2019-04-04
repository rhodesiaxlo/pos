<?php

namespace App\Models\Salesman;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table='crm_store';
    protected $primaryKey='id';
    public $timestamps=false;
}
