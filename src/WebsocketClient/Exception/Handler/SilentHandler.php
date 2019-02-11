<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 17:24
 */

namespace WebsocketClient\Exception\Handler;

use WebsocketClient\Exception\WebsocketException;

class SilentHandler implements WebsocketExceptionHandler
{
    public function handleException(WebsocketException $e)
    {

    }

}