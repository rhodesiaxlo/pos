<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
	protected $table = "pos_bank";
    public $timestamps = false;
	// pos_user 一对多关系
	public function users()
	{
		$this->hasMany('App\Models\User');
	}

	public function outflows()
	{
		$this->hasMany('App\Models\OutflowLog');
	}

	public function getAllBanks()
	{
		return $this->all();
	}
}
