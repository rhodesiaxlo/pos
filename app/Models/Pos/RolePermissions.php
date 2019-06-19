<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class RolePermissions extends Model
{
    protected $table = 'pos_role_permissions';
    public $timestamps = false;
    protected $primaryKey = "id";
}
