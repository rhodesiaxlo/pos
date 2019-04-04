<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-09-28
 * Time: 11:13
 */

namespace App\Tool\Statistics\Facades;

use Illuminate\Support\Facades\Facade;

class DataStatis extends Facade
{
   protected static function getFacadeAccessor(){

     return 'datastatis';
   }
}