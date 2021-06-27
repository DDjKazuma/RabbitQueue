<?php

namespace DDjkazuma\RabbitQueue\Queue;

use DDjkazuma\RabbitQueue\Job\RabbitJob;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitQueue extends Queue implements QueueContract
{

    private $rabbitConnection;
    private $channel;
    private $queueName;
    private $exchangeName;
    private $routingKey;

    public function __construct(AMQPStreamConnection $rabbitConnection, $queueName, string $exchangeName, string $routingKey)
    {
        $this->rabbitConnection = $rabbitConnection;
        $this->queueName = $queueName;
        $this->exchangeName = $exchangeName;
        $this->routingKey = $routingKey;
        $this->channel = $this->rabbitConnection->channel();
        $this->declareExchange();
        $this->declareQueue();
        $this->declareBinding();
    }

    public function size($queue = null)
    {
        return $this->declareQueue()[1];
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue, $data),
            $queue,
            null,
            function ($payload, $queue) {
                return $this->pushRaw($payload, $queue);
            }
        );

    }


    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->channel->basic_publish(new AMQPMessage($payload, $options), $this->exchangeName, $this->routingKey);
        return json_decode($payload, true)['id'] ?? null;
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue, $data),
            $queue,
            $delay,
            function($payload, $queue, $delay){
                return $this->pushRaw($payload, $queue, [
                    'delivery_mode' => 2,
                    'application_headers'=>new AMQPTable([
                        'x-delay'=>$delay * 1000,
                    ])
                ]);
            }
        );
        return json_decode($payload, true)['id'] ?? null;
    }

    public function pop($queue = null)
    {
        $message = $this->channel->basic_get($this->queueName, false);
        if($message === null){
            return null;
        }
        return new RabbitJob($this->container, $message, $this, $this->connectionName, $this->queueName);
    }

    private function declareQueue():array{
        return $this->channel->queue_declare($this->queueName, false, true, false, false);
    }

    private function declareExchange():void{

        $this->channel->exchange_declare($this->exchangeName, 'direct', false, true, false);
    }

    private function declareBinding():void{

        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);
    }


    protected function createPayloadArray($job, $queue, $data = '')
    {
        return array_merge(parent::createPayloadArray($job, $queue, $data), [
            'id' => $this->getRandomId(),
            'attempts' => 0,
        ]);
    }

    protected function getRandomId()
    {
        return Str::random(32);
    }
}