# RabbitQueue
#### A simple laravel RabbitMQ queue driver

# 使用方法
1. install this package by composer 
``` composer require ddjkazuma/rabbitqueue ```
2. add some config in config/queue.php
```php
    //specify your connection name here
    'rabbit_demo'=>[
            'driver'=>'rabbit',//driver must by rabbit
            'connection'=>'default',
            'host'=>'localhost',
            'port'=>5672,
            'username'=>'guest',
            'password'=>'guest',
            'queue'=>'privilege_demo',//specify your queue name here 
            'exchange'=>'basic_exchange',
            'routing_key'=>'privilege_demo',
        ]
```
3. use rabbit queue in your application code
```php
    dispatch(new DemoJob())->onConnection('rabbit_demo')->onQueue('privilege_demo');
```
