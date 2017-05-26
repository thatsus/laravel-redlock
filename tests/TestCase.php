<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as Base;

class TestCase extends Base
{

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        //$this->setTestEnvironment();

        // don't want these kinds of error notifications
        error_reporting(
            E_ALL 
            & ~ E_NOTICE 
            //& ~ E_WARNING 
            //& ~ E_DEPRECATED 
            //& ~ E_STRICT
        );

        return $app;
    }
}
