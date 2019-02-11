<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 17:24
 */

namespace WebsocketClient\Exception\Handler;

use Psr\Log\LoggerInterface;
use WebsocketClient\Exception\WebsocketException;

class LogHandler implements WebsocketExceptionHandler
{

    /** @var LoggerInterface */
    private $logger;

    /**
     * LogHandler constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleException(WebsocketException $e)
    {
        $this->logger->error($e->getMessage());
    }


}