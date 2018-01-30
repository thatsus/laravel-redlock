<?php

use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase as Base;

class TestCase extends Base
{
    protected function getPackageProviders($app)
    {
        return ['ThatsUs\\RedLock\\RedLockServiceProvider'];
    }  
}
