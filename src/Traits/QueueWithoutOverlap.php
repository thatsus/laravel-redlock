<?php

namespace ThatsUs\RedLock\Traits;

use ThatsUs\RedLock\Facades\RedLock;
use Illuminate\Database\Eloquent\Model;
use ThatsUs\RedLock\Exceptions\QueueWithoutOverlapRefreshException;

trait QueueWithoutOverlap
{
    protected $lock;

    /**
     * Put this job on that queue. Or don't 
     * if we fail to acquire the lock.
     * @return string|bool - queue code or false
     */
    public function queue($queue, $command)
    {
        if (!method_exists($this, 'handleSync')) {
            throw new \Exception('Please define handleSync() on the job ' . get_class($this) . '.');
        }
        if ($this->acquireLock()) {
            return $this->pushCommandToQueue($queue, $command);
        } else {
            // do nothing, could not get lock
            return false;
        }
    }

    /**
     * Lock this job's key in redis, so no other 
     * jobs can run with the same key.
     * @return bool - false if it fails to lock
     */
    protected function acquireLock(array $lock = [])
    {
        $lock_time = isset($this->lock_time) ? $this->lock_time : 300; // in seconds; 5 minutes default
        $this->lock = RedLock::lock($lock['resource'] ?? $this->getLockKey(), $lock_time * 1000);
        return (bool)$this->lock;
    }

    /**
     * Unlock this job's key in redis, so other
     * jobs can run with the same key.
     * @return void
     */
    protected function releaseLock()
    {
        if ($this->lock) {
            RedLock::unlock($this->lock);
        }
    }

    /**
     * Build a unique key based on the values stored in this job.
     * Any job with the same values is assumed to represent the same
     * task and so will not overlap this.
     * 
     * Override this method if necessary.
     * 
     * @return string
     */
    protected function getLockKey()
    {
        $values = collect((array)$this)
            ->values()
            ->map(function ($value) {
                if ($value instanceof Model) {
                    return $value->id;
                } else if (is_object($value) || is_array($value)) {
                    throw new \Exception('This job cannot auto-generate a lock-key. Please define getLockKey() on ' . get_class($this) . '.');
                } else {
                    return $value;
                }
            });
        return get_class($this) . ':' . $values->implode(':');
    }

    /**
     * This code is copied from Illuminate\Bus\Dispatcher v5.4
     * @ https://github.com/laravel/framework/blob/5.4/src/Illuminate/Bus/Dispatcher.php#L163
     * 
     * Push the command onto the given queue instance.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $queue
     * @param  mixed  $command
     * @return mixed
     */
    protected function pushCommandToQueue($queue, $command)
    {
        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $command);
        }
        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $command);
        }
        if (isset($command->delay)) {
            return $queue->later($command->delay, $command);
        }
        return $queue->push($command);
    }

    /**
     * Normal jobs are called via handle. Use handleSync instead.
     * @return void
     */
    public function handle()
    {
        try {
            $this->handleSync();
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Attempt to reacquire and extend the lock.
     * @return bool true if the lock is reacquired, false if it is not
     */
    protected function refreshLock()
    {
        $this->releaseLock();
        if (!$this->acquireLock($this->lock)) {
            throw new QueueWithoutOverlapRefreshException();
        }
    }
}
