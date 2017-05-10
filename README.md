# Laravel RedLock
一个基于laravel框架的redis分布式锁。一般用于高并发抢单，支付等原子性操作的锁操作。

### 关于

larave-redislock 是在[php-redlock](https://github.com/ronnylt/redlock-php)基础上将redis扩展改为predis插件后为larave进行封装的分布式锁。
欢迎大家提交反馈

### 使用说明

1. 使用 `comporse require libichai/laravel-redlock` 载入项目依赖
2. 添加服务器提供器,将 `LibiChai\RedLock\RedLockServiceProvider::class,` 添加到config/app.php的 `providers` 中
3. 可选添加门面 `'RedLock'=>LibiChai\RedLock\RedLockFactory::class,` 到config/app.php的 `aliases` 中
4. 使用实例
 ```php
 
 use RedLock;

//假设product为抢购商品 
 $product_id = 1;
 
//使用商品id作为锁键
 $locktoken = RedLock::lock($product_id);
  
//执行库存判断 下单等操作
 $order->submit($product_id,$user); 

//解除锁定
 RedLock::unlock($locktoken); 
 
 ```
