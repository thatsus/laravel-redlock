<?php

namespace ThatsUs\RedLock;

use Illuminate\Support\ServiceProvider;
use ThatsUs\RedLock\RedLock;

class RedLockServiceProvider extends ServiceProvider{
    /**
     * bootstrap, add routes
     */
    public function boot()
    {

    }

    /**
     * register the service provider
     */
    public function register()
    {
        // store to container
        $this->app->singleton('redlock', function ($app) {
            return new RedLock(
                $app->make('config')->get('database.redis'),
                $app->make('config')->get('database.redis.options.redis_lock.retry_delay'),
                $app->make('config')->get('database.redis.options.redis_lock.retry_count'),
                
            );
        });
    }
}
