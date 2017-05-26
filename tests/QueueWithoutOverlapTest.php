<?php

namespace ThatsUs\RedLock\Traits;

use ThatsUs\RedLock\Facades\RedLock;
use Mockery;
use TestCase;

class QueueWithoutOverlapTest extends TestCase
{
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
            ->with("ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300", 300000)
            ->twice()
            ->andReturn(['resource' => 'ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300']);
        RedLock::shouldReceive('unlock')
            ->with(['resource' => 'ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300'])
            ->twice()
            ->andReturn(true);

        $job->queue($queue, $job);

        $job->handle();

        $this->assertTrue($job->ran);
    }

    public function testFailToLock()
    {
        $job = new QueueWithoutOverlapJob();

        $queue = Mockery::mock();

        RedLock::shouldReceive('lock')
            ->with("ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300", 300000)
            ->once()
            ->andReturn(false);

        $id = $job->queue($queue, $job);

        $this->assertFalse($id);
    }

    public function testFailToRefresh()
    {
        $job = new QueueWithoutOverlapJob();

        $queue = Mockery::mock();
        $queue->shouldReceive('push')->with($job)->once();

        RedLock::shouldReceive('lock')
            ->with("ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300", 300000)
            ->twice()
            ->andReturn(
                ['resource' => 'ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300'],
                false
            );
        RedLock::shouldReceive('unlock')
            ->with(['resource' => 'ThatsUs\RedLock\Traits\QueueWithoutOverlapJob:::300'])
            ->once()
            ->andReturn(true);

        $job->queue($queue, $job);

        $this->expectException('ThatsUs\RedLock\Exceptions\QueueWithoutOverlapRefreshException');

        $job->handle();
    }
}
