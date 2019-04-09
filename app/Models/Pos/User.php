<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    
    protected $table = 'pos_user';
    

    /**
     * 和  pos_user 多对一关系
     * @return [type] [description]
     */
    public function bank()
    {
    	return $this->belongsTo('App\Models\Pos\Bank');
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
}
