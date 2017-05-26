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
 use ThatsUs\RedLock\Facades\RedLock;

 $product_id = 1;

 $lock_token = RedLock::lock($product_id, 3000);
 
 if ($lock_token) {

     $order->submit($product_id);

     RedLock::unlock($lock_token);
 }
```

### Refresh

Use `refreshLock()` to reaquire and extend the time of your lock.

```php 
 use ThatsUs\RedLock\Facades\RedLock;

 $product_ids = [1, 2, 3, 5, 7];

 $lock_token = RedLock::lock('order-submitter', 3000);
 
 while ($lock_token) {

     $order->submit(array_shift($product_ids));

     $lock_token = RedLock::refreshLock($lock_token);
 }

 RedLock::unlock($lock_token);
```

### Even Easier with Closures

Use `runLocked()` for nicer syntax. The method returns the results of the closure, or else false if the lock could not be aquired.

```php
 use ThatsUs\RedLock\Facades\RedLock;

 $product_id = 1;

 $result = RedLock::runLocked($product_id, 3000, function () use ($order, $product_id) {
     $order->submit($product_id);
     return true;
 });

 echo $result ? 'Worked!' : 'Lock not aquired.';
```

### Refresh with Closures

You can refresh your tokens with closures too. The first parameter to your closure is a $refresh closure. Call it to refresh. If the lock cannot be refreshed, the closure will return.

```php
 use ThatsUs\RedLock\Facades\RedLock;

 $product_ids = [1, 2, 3, 5, 7];

 $result = RedLock::runLocked($product_id, 3000, function ($refresh) use ($order, $product_ids) {
     foreach ($product_ids as $product_id) {
         $refresh();
         $order->submit($product_id);
     }
     return true;
 });

 echo $result ? 'Worked!' : 'Lock lost or never aquired.';
```




