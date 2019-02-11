<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 17:14
 */

namespace WebsocketClient\Exception\Handler;

use WebsocketClient\Exception\WebsocketException;

class ThrowHandler implements WebsocketExceptionHandler
{
    public function handleException(WebsocketException $e)
    {
        throw $e;
    }


}