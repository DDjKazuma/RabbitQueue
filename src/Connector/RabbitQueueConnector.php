<?php


namespace DDjkazuma\RabbitQueue\Connector;


use DDjkazuma\RabbitQueue\Queue\RabbitQueue;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;

class RabbitQueueConnector implements ConnectorInterface
{
    public function connect(array $config): RabbitQueue
    {
        $connection = app('rabbit.connector')
            ->connect(
                $config['connection'],
                Arr::only($config, ['host', 'port', 'username','password'])
            );
        return new RabbitQueue(
            $connection,
            $config['queue'],
            $config['exchange'],
            $config['routing_key']
        );
    }
}