<?php namespace Ace\RepoManUi;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;;

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
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

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
     *
     */
    private function connect()
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection($this->host, $this->port, 'guest', 'guest');
            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare($this->channel_name, 'fanout', false, false, false);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->channel->close();
            $this->connection->close();
        }
    }

    /**
     * @param array $event
     */
    public function publish(array $event)
    {
        $this->connect();

        $msg = new AMQPMessage(json_encode($event, JSON_UNESCAPED_SLASHES), [
            'content_type' => 'application/json',
            'timestamp' => time()
        ]);

        $this->channel->basic_publish($msg, '', $this->channel_name);

    }
}