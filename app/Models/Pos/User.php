<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    
    protected $table = 'pos_user';
    public $timestamps = false;
    protected $primaryKey = "local_id";


    /**
     * 和  pos_user 多对一关系
     * @return [type] [description]
     */
    public function bank()
    {
    	return $this->belongsTo('App\Models\Pos\Bank');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\Admin\AdminUser', 'created_by', 'id');   
    }

    /**
     * region 表的多对一关系
     * @return [type] [description]
     */
    public function province()
    {
    	return $this->belongsTo('App\Models\Pos\Region', 'province_id', 'region_id');
    }

    public function city()
    {
    	return $this->belongsTo('App\Models\Pos\Region', 'city_id', 'region_id');
    }

    public function area()
    {
    	return $this->belongsTo('App\Models\Pos\Region', 'area_id', 'region_id');
    }

    /**
     * 生成随机订单编号
     * @return [type] [description]
     */
    public static  function getRandomStoreCode()
    {
        //JM+7位数字
        //ZY
            return "JM".strval(time());
    }
}
