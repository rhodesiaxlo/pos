<?php

namespace App\Models\Videos;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Tool\HttpSet\HttpCurl;

class UserAddress extends Model
{
    protected $primaryKey = 'address_id';
    protected $table = 'dsc_user_address';
    protected $connection = 'mysql_DH';
    public $timestamps = false;

    public static function getAddress($result)
    {
        $lat=' CASE address_id ';
        $lng=' CASE address_id';
        $address=[];
        if (!empty($result)) {
            foreach ($result as $k => $v) {
                $v->address = str_replace(' ', '', $v->address);
                $set = HttpCurl::BaiDuApi($v->address);
                if (!empty($set) && $set['Ydata'] > 0 && $set['Xdata'] > 0) {
                    $lat .= '  when ' . $v->address_id . ' THEN ' . $set['Ydata'];
                    $lng .= '  when ' . $v->address_id . ' THEN ' . $set['Xdata'];
                    $address[] = $v->address_id;
                } else {
                    continue;
                }
            }
            $address = implode(',', $address);
            $sql = 'update dsc_user_address set lat=(' . $lat . ' END), lng=(' . $lng . ' END) where address_id in (' . $address . ')';
            $message=DB::connection('mysql_DH')->update($sql);
        }else{
            $message=false;
        }
        return $message;
    }
}
