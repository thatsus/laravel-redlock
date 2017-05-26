<?php

namespace ThatsUs\RedLock;

use Mockery;
use TestCase;
use Predis\Client as Redis;
use Illuminate\Support\Facades\App;

class RedLockTest extends TestCase
{
    public function testInstanciate()
    {
        new RedLock([]);
    }

    public function testLock()
    {
        $predis = Mockery::mock(Redis::class);
        $predis->shouldReceive('set')
            ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
            ->once()
            ->andReturn(true);
        App::instance(Redis::class, $predis);

        $redlock = new RedLock([['tester']]);
        $lock = $redlock->lock('XYZ', 300000);

        $this->assertEquals('XYZ', $lock['resource']);
        $this->assertTrue(is_numeric($lock['validity']));
        $this->assertNotNull($lock['token']);
    }

    public function testUnlock()
    {
        $predis = Mockery::mock(Redis::class);
        $predis->shouldReceive('eval')
            ->with(Mockery::any(), 1, 'XYZ', '1234')
            ->once()
            ->andReturn(true);
        App::instance(Redis::class, $predis);

        $redlock = new RedLock([['tester']]);
        $redlock->unlock([
            'resource' => 'XYZ', 
            'validity' => 300000,
            'token' => 1234,
        ]);
    }

    public function testLockFail()
    {
        $predis = Mockery::mock(Redis::class);
        $predis->shouldReceive('set')
            ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
            ->times(3)
            ->andReturn(false);
        $predis->shouldReceive('eval')
            ->with(Mockery::any(), 1, 'XYZ', Mockery::any())
            ->times(3)
            ->andReturn(true);
        App::instance(Redis::class, $predis);

        $redlock = new RedLock([['tester']]);
        $lock = $redlock->lock('XYZ', 300000);

        $this->assertFalse($lock);
    }

    public function testUnlockFail()
    {
        $predis = Mockery::mock(Redis::class);
        $predis->shouldReceive('eval')
            ->with(Mockery::any(), 1, 'XYZ', '1234')
            ->once()
            ->andReturn(false);
        App::instance(Redis::class, $predis);

        $redlock = new RedLock([['tester']]);
        $redlock->unlock([
            'resource' => 'XYZ', 
            'validity' => 300000,
            'token' => 1234,
        ]);
    }

    public function testRefresh()
    {
        $predis = Mockery::mock(Redis::class);
        $predis->shouldReceive('eval')
            ->with(Mockery::any(), 1, 'XYZ', '1234')
            ->once()
            ->andReturn(true);
        $predis->shouldReceive('set')
            ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
            ->once()
            ->andReturn(true);
        App::instance(Redis::class, $predis);

        $redlock = new RedLock([['tester']]);
        $lock = $redlock->refreshLock([
            'resource' => 'XYZ', 
            'validity' => 300000,
            'token' => 1234,
            'ttl' => 300000,
        ]);

        $this->assertEquals('XYZ', $lock['resource']);
        $this->assertTrue(is_numeric($lock['validity']));
        $this->assertNotNull($lock['token']);
    }
}
