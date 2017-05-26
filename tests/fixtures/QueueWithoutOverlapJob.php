<?php

namespace App\Traits;

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

    public function handleSync()
    {
        $this->ran = true;
    }
}
