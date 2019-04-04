<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Tool\Statistics\Services\DataStatis;

class DataStatisProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('datastatis',function(){
            return new DataStatis();
        });
    }
}
