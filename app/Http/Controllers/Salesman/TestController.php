<?php

namespace App\Http\Controllers\Salesman;

use Faker\Provider\DateTime;
use App\Events\permChangeEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Salesman\Salesman;
use Carbon\Carbon;
use DB;
use Cache,Auth,Event;
use Illuminate\Support\Facades\Session;

class TestController extends Controller
{
    
}
