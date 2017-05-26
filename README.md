# Laravel RedLock

Provides a generic locking mechanism using Redis. Implements the locking standard proposed by Redis.



### Acknowledgements

This library was originally built by LibiChai based on the Redlock algorithm developed by antirez. The library was reworked by the team at That's Us, Inc.

### Installation

1. `composer require thatsus/laravel-redlock`
2. Add `ThatsUs\RedLock\RedLockServiceProvider::class,` to the `providers` array in config/app.php
3. Enjoy!


### It's Simple!

Set a lock on any scalar. If the `lock()` method returns false, you did not aquire the lock.

Store results of the `lock()` method. Use this value to release the lock with `unlock()`.

### Example

This example sets a lock on the key "1" with a 3 second expiration time.

If it aquired the lock, it does some work and releases the lock.

```php 
 use ThatsUs\Facades\RedLock;

 $product_id = 1;

 $lock_token = RedLock::lock($product_id, 3000);
 
 if ($lock_token) {

     $order->submit($product_id, $user); 

     RedLock::unlock($lock_token);
 }
```
