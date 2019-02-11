<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 10:59
 */

namespace WebsocketClient\Exception;

class WebsocketReaderException extends WebsocketException
{

    /**
     * @param string $context
     *
     * @return WebsocketReaderException
     */
    public static function headerError(string $context = '')
    {
        return new static(sprintf('Cannot read %s headers from websocket!', $context));
    }

    /**
     * @return WebsocketReaderException
     */
    public static function generalError()
    {
        return new static('Cannot read frame data!');
    }

}