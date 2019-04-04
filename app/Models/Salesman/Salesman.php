<?php

namespace App\Models\Salesman;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    /**
     * 外联别的数据库
     * protected $connection = 'connection-name';
     */
    protected $table='crm_salesman';
    protected $primaryKey='id';
    public $timestamps=false;
    protected $fillable = ['salens_name','sex','mobile','salens_type','add_time','update_time','operator','ramk','is_active','is_del'];
}
