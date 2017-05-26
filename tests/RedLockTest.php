<?php

namespace ThatsUs\RedLock;

use Mockery;
use TestCase;

class RedLockTest extends TestCase
{
    public function testInstanciate()
    {
        new RedLock([]);
    }
}
