<?php

require __DIR__ . '/vendor/autoload.php';

use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

$loop = React\EventLoop\Factory::create();
$server = new HttpServer($loop, function (ServerRequestInterface $request) {
    $path = __DIR__ . '/public' . $request->getUri()->getPath();

    if (file_exists($path) && is_file($path)) {
        $content = file_get_contents($path);
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $content
        );
    }

    return new Response(
        404,
        ['Content-Type' => 'text/plain'],
        "Page not found\n"
    );
});

$socket = new React\Socket\SocketServer('0.0.0.0:8080', [], $loop);
$server->listen($socket);

echo "Server running at http://0.0.0.0:8080\n";

$loop->run();
