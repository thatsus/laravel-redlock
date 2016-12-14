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
        //publish a config file
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('redlock.php'),
        ], 'config');


    }

    /**
     * register the service provider
     */
    public function register()
    {
        // merge configs
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'redlock');

        // store to container
        $this->app->singleton('redlock', function ($app) {
            return new RedLock($this->config('serivers'),$this->config('retry_delay'),$this->config('retry_count'));
        });
    }

    /**
     * Helper to get the config values.
     *
     * @param  string $key
     * @return string
     */
    protected function config($key, $default = null)
    {
        return config("redlock.$key", $default);
    }
}