<?php
return [
    //redis服务器集群
    'serivers'=>[
        [
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 8,
        ],  //服务器地址  端口  数据库
    ],

    //重试间隔 ms
    'retry_delay'=>200,

    //重试次数
    'retry_count'=>3

];
