# Laravel RedLock

Provides a locking mechanism using Redis. Implements the locking standard proposed by Redis.

# Acknowledgement

This library was originally built by LibiChai, then reworked by the team at That's Us, Inc.

### Installation

1. `composer require thatsus/laravel-redlock`
2. Add `ThatsUs\RedLock\RedLockServiceProvider::class,` to the `providers` array in config/app.php
3. Add `'RedLock' => Thatsus\RedLock\RedLockFactory::class,` to the `aliases` array in config/app.php
4. Enjoy!


### Example

 ```php 
 use ThatsUs\Facades\RedLock;

 $product_id = 1;

 $locktoken = RedLock::lock($product_id);

 $order->submit($product_id, $user); 

 RedLock::unlock($locktoken); 
 ```
