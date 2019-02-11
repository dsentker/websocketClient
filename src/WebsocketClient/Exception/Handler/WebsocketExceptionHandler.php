<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 17:15
 */

namespace WebsocketClient\Exception\Handler;


use WebsocketClient\Exception\WebsocketException;

interface WebsocketExceptionHandler
{
    public function handleException(WebsocketException $e);
}