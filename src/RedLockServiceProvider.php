<?php
/**
 * Created by PhpStorm.
 * User: libi
 * Date: 16/12/14
 * Time: 下午12:15
 */
namespace LibiChai\RedLock;

use Illuminate\Support\ServiceProvider;
use LibiChai\RedLock\RedLock;

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
                [config('database.redis.default')], 
                config('database.redis.redis_lock.retry_delay'), 
                config('database.redis.redis_lock.retry_count')
            );
        });
    }
}
