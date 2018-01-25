<?php

namespace ThatsUs\RedLock;

use Mockery;
use TestCase;
use Predis\Client as Redis;
use Illuminate\Support\Facades\App;

class RedLockTest extends TestCase
{
    private $servers = [
        [
            'host'     => 'host.test',
            'password' => 'password',
            'port'     => 6379,
            'database' => 0,
        ],
    ];

    public function testInstanciate()
    {
        new RedLock([]);
    }

    public function testLock()
    {
        App::bind(Redis::class, function() {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->once()
                ->andReturn(true);
            return $predis; 
        });

        $redlock = new RedLock($this->servers);
        $lock = $redlock->lock('XYZ', 300000);

        $this->assertEquals('XYZ', $lock['resource']);
        $this->assertTrue(is_numeric($lock['validity']));
        $this->assertNotNull($lock['token']);
    }

    public function testUnlock()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', '1234')
                ->once()
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $redlock->unlock([
            'resource' => 'XYZ', 
            'validity' => 300000,
            'token' => 1234,
        ]);
    }

    public function testLockFail()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->times(3)
                ->andReturn(false);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', Mockery::any())
                ->times(3)
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $lock = $redlock->lock('XYZ', 300000);

        $this->assertFalse($lock);
    }

    public function testUnlockFail()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', '1234')
                ->once()
                ->andReturn(false);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $redlock->unlock([
            'resource' => 'XYZ', 
            'validity' => 300000,
            'token' => 1234,
        ]);
    }

    public function testRefresh()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', '1234')
                ->once()
                ->andReturn(true);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->once()
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
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

    public function testRunLocked()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->once()
                ->andReturn(true);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', Mockery::any())
                ->once()
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $results = $redlock->runLocked('XYZ', 300000, function () {
            return "ABC";
        });

        $this->assertEquals('ABC', $results);
    }

    public function testRunLockedRefresh()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->twice()
                ->andReturn(true);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', Mockery::any())
                ->twice()
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $results = $redlock->runLocked('XYZ', 300000, function ($refresh) {
            $refresh();
            return "ABC";
        });

        $this->assertEquals('ABC', $results);
    }

    public function testRunLockedRefreshFail()
    {
        App::bind(Redis::class, function () {
            $predis = Mockery::mock(Redis::class);
            $predis->shouldReceive('set')
                ->with('XYZ', Mockery::any(), "PX", 300000, "NX")
                ->times(4)
                ->andReturn(true, false, false, false);
            $predis->shouldReceive('eval')
                ->with(Mockery::any(), 1, 'XYZ', Mockery::any())
                ->times(4)
                ->andReturn(true);
            return $predis;
        });

        $redlock = new RedLock($this->servers);
        $results = $redlock->runLocked('XYZ', 300000, function ($refresh) {
            $refresh();
            return "ABC";
        });

        $this->assertFalse($results);
    }
}
