<?php namespace Ace\RepoManUi;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author timrodger
 * Date: 05/12/15
 */
class RabbitClient
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $channel_name;

    /**
     * @param $host
     * @param $port
     * @param $channel_name
     */
    public function __construct($host, $port, $channel_name)
    {
        $this->host = $host;
        $this->port = $port;
        $this->channel_name = $channel_name;
    }

    /**
     * @param array $event
     */
    public function publish(array $event)
    {
        $connection = new AMQPStreamConnection($this->host, $this->port, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare($this->channel_name, false, false, false, false);

        $msg = new AMQPMessage(json_encode($event, JSON_UNESCAPED_SLASHES), [
            'content_type' => 'application/json',
            'timestamp' => time()
        ]);
        $channel->basic_publish($msg, '', $this->channel_name);

        $channel->close();
        $connection->close();

    }
}