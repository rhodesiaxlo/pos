<?php

namespace App\Http\Middleware;

use Closure;
use Auth, Cache;

use DB;


class BeforeAnyDbQueryMiddleware
{
    public function handle($request, Closure $next)
    {
        DB::enableQueryLog();
        return $next($request);
    }

    public function terminate($request, $response)
    {
        // Store or dump the log data...
        dd(
            DB::getQueryLog()
        );
    }
}