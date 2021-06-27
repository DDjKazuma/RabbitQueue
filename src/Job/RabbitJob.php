<?php

namespace DDjkazuma\RabbitQueue\Job;

use DDjkazuma\RabbitQueue\Queue\RabbitQueue;
use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitJob extends Job implements JobContract
{
    private $decoded;
    private $rabbitQueue;
    private $message;


    public function __construct(Container $container, AMQPMessage $message, RabbitQueue $rabbitQueue, string
    $connection, string $queue){
        $this->container = $container;
        $this->message = $message;
        $this->rabbitQueue = $rabbitQueue;
        $this->queue = $queue;
        $this->connectionName = $connection;
        $this->decoded = $this->payload();
    }

    public function getJobId()
    {

        return $this->decoded['id']?:null;
    }


    public function getRawBody()
    {
        return $this->message->getBody();
    }

    public function attempts()
    {
        return ($this->decoded['attempts'] ?? null) + 1;
    }

    public function delete()
    {
        $this->message->ack();
        parent::delete();
    }

    public function release($delay = 0)
    {
        $this->message->reject(true);
        parent::release($delay);
    }

}