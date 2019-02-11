<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 11.02.2019
 * Time: 10:26
 */

namespace WebsocketClient;


use Symfony\Component\HttpFoundation\HeaderBag;
use WebsocketClient\Exception\WebsocketException;
use WebsocketClient\Exception\WebsocketReaderException;

/**
 * Class WebsocketClient
 * Inspired by package "Websocket client for PHP" ( via https://github.com/paragi/PHP-websocket-client )
 *
 * @package WebsocketClient
 */
class WebsocketClient
{

    /** @var HeaderBag */
    private $headerBag;

    /** @var resource */
    private $socket;

    /**
     * WebsocketClient constructor.
     *
     * @param string $host    A host URL. It can be a domain name like www.example.com or an IP address, with port
     *                        number.
     * @param int    $port
     * @param int    $timeout The maximum time in seconds, a read operation will wait for an answer from the server.
     * @param array  $headers Additional HTTP headers (Key-Value pair) to attach to the request.
     *
     * @throws WebsocketException
     */
    public function __construct(string $host, int $port, int $timeout = 10, array $headers = [])
    {
        // Generate a key (to convince server that the update is not random)
        $key = base64_encode(uniqid());

        $this->headerBag = new HeaderBag([
            'Host'                  => $host,
            'pragma'                => 'no-cache',
            'Upgrade'               => 'WebSocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Key'     => $key,
            'Sec-WebSocket-Version' => '13'
        ]);

        $this->headerBag->add($headers);

        $this->socket = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            throw WebsocketException::connectionError($host, $errno, $errstr);
        }

        stream_set_timeout($this->socket, $timeout);

        $this->requestUpgrade();


    }

    /**
     * @param string $content Data to transport to server
     * @param bool   $final   indicate if this block is the final data block of this request. Default true
     *
     * @return bool|int
     */
    public function write(string $content, $final = true)
    {

        $messageLength = mb_strlen($content);

        $header = chr(($final ? 0x80 : 0) | 0x02); // 0x02 binary
        // Mask 0x80 | payload length (0-125)
        if ($messageLength < 126) {
            $header .= chr(0x80 | $messageLength);
        } elseif ($messageLength < 0xFFFF) {
            $header .= chr(0x80 | 126) . pack("n", $messageLength);
        } else {
            $header .= chr(0x80 | 127) . pack("N", 0) . pack("N", $messageLength);
        }

        // Add mask
        $mask = pack("N", rand(1, 0x7FFFFFFF));
        $header .= $mask;

        // Mask application data.
        for ($i = 0; $i < $messageLength; $i++)
            $content[$i] = chr(ord($content[$i]) ^ ord($mask[$i % 4]));

        return fwrite($this->socket, $header . $content);
    }

    /**
     * @return string
     *
     * @throws WebsocketReaderException
     */
    public function read()
    {
        $data = '';

        do {
            // Read header
            $header = fread($this->socket, 2);
            if (!$header) {
                throw WebsocketReaderException::headerError();
            }

            $opcode = ord($header[0]) & 0x0F;
            $final = ord($header[0]) & 0x80;
            $masked = ord($header[1]) & 0x80;
            $payloadLength = ord($header[1]) & 0x7F;

            // Get payload length extensions

            if ($payloadLength >= 0x7E) {
                $extLength = 2;
                if ($payloadLength == 0x7F) {
                    $extLength = 8;
                }
                $header = fread($this->socket, $extLength);
                if (!$header) {
                    throw WebsocketReaderException::headerError('Extension');
                }

                // Set extended payload length
                $payloadLength = 0;
                for ($i = 0; $i < $extLength; $i++)
                    $payloadLength += ord($header[$i]) << ($extLength - $i - 1) * 8;
            }

            // Get Mask key
            $mask = null;
            if ($masked) {
                $mask = fread($this->socket, 4);
                if (!$mask) {
                    throw WebsocketReaderException::headerError('Mask');
                }
            }

            // Get payload
            $frameData = '';
            do {
                $frame = fread($this->socket, $payloadLength);
                if (!$frame) {
                    throw WebsocketReaderException::generalError();
                }
                $payloadLength -= strlen($frame);
                $frameData .= $frame;
            } while ($payloadLength > 0);

            // Handle ping requests (sort of) send pong and continue to read
            if ($opcode == 9) {
                // Assemble header: Final 0x80 | Opcode 0x0A + Mask on 0x80 with zero payload
                fwrite($this->socket, chr(0x8A) . chr(0x80) . pack("N", rand(1, 0x7FFFFFFF)));
                continue;

                // Close
            } elseif ($opcode == 8) {
                fclose($this->socket);
                // 0 = continuation frame, 1 = text frame, 2 = binary frame
            } elseif ($opcode < 3) {
                // Unmask data
                $dataLength = strlen($frameData);
                if ($masked)
                    for ($i = 0; $i < $dataLength; $i++)
                        $data .= $frameData[$i] ^ $mask[$i % 4];
                else
                    $data .= $frameData;

            } else
                continue;

        } while (!$final);

        return $data;
    }

    /**
     * @param $headerString
     * @param $search
     *
     * @return bool
     */
    private static function headerStringContains($headerString, $search)
    {
        return false !== strpos($headerString, $search);
    }

    /**
     * @return string The response from websocket
     *
     * @throws WebsocketException
     */
    protected function requestUpgrade()
    {


        //Request upgrade to websocket
        $headerString = sprintf("GET / HTTP/1.1\r\n%s", $this->headerBag->__toString());
        $rc = fwrite($this->socket, $headerString);
        if (!$rc) {
            throw WebsocketException::upgradeRequestError($this->headerBag->get('Host'));
        }

        // Read response into an associative array of headers. Fails if upgrade fails.
        $responseHeader = fread($this->socket, 1024);

        if (
            (!$responseHeader) ||
            (!self::headerStringContains($responseHeader, ' 101 ')) ||
            (!self::headerStringContains($responseHeader, ' Sec-Websocket-Accept: '))
        ) {
            throw WebsocketException::upgradeResponseError($responseHeader);
        }

        // The key we send is returned, concatenate with "258EAFA5-E914-47DA-95CA-
        // C5AB0DC85B11" and then base64-encoded. one can verify if one feels the need...

        return $responseHeader;

    }

}