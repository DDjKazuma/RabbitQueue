<?php

namespace DDjkazuma\RabbitQueue\RabbitMQ;



use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnector
{

    private $connections = [];

    public function connect(string $connectionName, $config){

        return  $this->connections[$connectionName] ?? $this->connections[$connectionName] =
            $this->createConnection($config);
    }

    private function createConnection(array $config): AMQPStreamConnection
    {
        return new AMQPStreamConnection($config['host'], $config['port'], $config['username'], $config['password']);
    }

}