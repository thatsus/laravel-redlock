<?php

namespace App\Traits;

use Laravel\Lumen\Testing\DatabaseTransactions;
use ThatsUs\RedLock\Facades\RedLock;
use Mockery;
use Tests\TestCase;

class QueueWithoutOverlapTest extends TestCase
{
    use DatabaseTransactions;

    public function testInstanciate()
    {
        new QueueWithoutOverlapJob();
    }

    public function testAllOfIt()
    {
        $job = new QueueWithoutOverlapJob();

        $queue = Mockery::mock();
        $queue->shouldReceive('push')->with($job)->once();

        RedLock::shouldReceive('lock')
            ->with("App\Traits\QueueWithoutOverlapJob:::300", 300000)
            ->once()
            ->andReturn(['this is a lock']);
        RedLock::shouldReceive('unlock')
            ->with(['this is a lock'])
            ->once()
            ->andReturn(true);

        $job->queue($queue, $job);

        $job->handle();

        $this->assertTrue($job->ran);
    }
}
