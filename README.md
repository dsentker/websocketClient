# WebsocketClient
A simple class to connect to a websocket service. It handles negotiation with the socket server and handles the hybi10 frame encoding.

## Requirements
- PHP >= 7.1.x
- An existing websocket server (e.g. [ws](https://github.com/websockets/ws) or something else )

## Install
```composer require dsentker/websocket-client``` (WIP)

## Usage
```php
$client = new WebsocketClient('localhost', 8080);
$client->write('Hello World!'); // sends a message to websocket server
$content = $client->read(); // Read from websocket
```

## Inspiration and Motivation
I have not found a working implementation for a websocket client in PHP. Many solutions were too complicated, required the installation of additional libraries or did not work at all. I came across [PHP-websockets](https://github.com/paragi/PHP-websocket-client) from paragi. This function set worked perfectly, but was not technically up to date. I have rewritten it for PHP 7.1 with an object-oriented approach and a composer repository.

## Submitting bugs and feature requests
Bugs and feature request are tracked on GitHub.

## Copyright and license
[Unlicensed](http://unlicense.org).