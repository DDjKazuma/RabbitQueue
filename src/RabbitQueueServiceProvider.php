<?php


namespace DDjkazuma\RabbitQueue;

use DDjkazuma\RabbitQueue\Connector\RabbitQueueConnector;
use DDjkazuma\RabbitQueue\RabbitMQ\RabbitMQConnector;
use Illuminate\Support\ServiceProvider;

class RabbitQueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('rabbit.connector', function(){
            return new RabbitMQConnector();
        });
    }

    public function boot(){
        $this->app->make('queue')->extend('rabbit', function(){
            return new RabbitQueueConnector();
        });
    }
}