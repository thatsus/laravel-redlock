<?php

namespace ThatsUs\RedLock\Traits;

/*
|--------------------------------------------------------------------------
| QueueWithoutOverlapJob
|--------------------------------------------------------------------------
|
| This class is for testing the WithoutOverlap trait. It just uses the 
| trait and that's all. This time without a $lock_time specified
|
*/

class QueueWithoutOverlapJobDefaultLockTime
{
    use QueueWithoutOverlap;

    public $ran = false;

    public function handleSync()
    {
        $this->refreshLock();
        $this->ran = true;
    }
}
