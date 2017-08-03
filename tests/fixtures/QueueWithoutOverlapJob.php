<?php

namespace ThatsUs\RedLock\Traits;

/*
|--------------------------------------------------------------------------
| QueueWithoutOverlapJob
|--------------------------------------------------------------------------
|
| This class is for testing the WithoutOverlap trait. It just uses the 
| trait and that's all.
|
*/

class QueueWithoutOverlapJob
{
    use QueueWithoutOverlap;

    public $ran = false;
    protected $lock_time = 1000;

    public function handleSync()
    {
        $this->refreshLock();
        $this->ran = true;
    }
}
