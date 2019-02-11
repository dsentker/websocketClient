<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 10:59
 */

namespace WebsocketClient\Exception;

class WebsocketWriterException extends WebsocketException
{

    /**
     * @param string $host
     * @param string $errorMessage
     * @param int    $errorNo
     *
     * @return WebsocketWriterException
     */
    public static function connectionError(string $host, string $errorMessage, int $errorNo)
    {
        return new static(sprintf('Cannot connect so socket "%s": %s (%d)', $host, $errorMessage, $errorNo));

    }

    /**
     * @param string $host
     *
     * @return WebsocketWriterException
     */
    public static function upgradeRequestError($host)
    {
        return new static(sprintf('"Unable to send upgrade header to websocket server "%s"!', $host));
    }

    /**
     * @param string $headerString
     *
     * @return WebsocketWriterException
     */
    public static function upgradeResponseError(string $headerString)
    {
        return new static(sprintf('Server did not accept to upgrade connection to websocket (response was "%s")!', $headerString));
    }

}