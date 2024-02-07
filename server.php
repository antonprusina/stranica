<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Socket\SocketServer;

$loop = Factory::create();

$server = new HttpServer($loop, function (ServerRequestInterface $request) {
    $uriPath = $request->getUri()->getPath();

    // Serve a simple "Hello World!" page
    if ($uriPath === '/hello') {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            '<html><body><h1>Hello World!</h1></body></html>'
        );
    }

    // Serve the `index.html` file from public directory
    if ($uriPath === '/') {
        $content = file_get_contents(__DIR__ . '/public/index.html');
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            $content
        );
    }

    // Dynamically serve files from the `public` directory with MIME type detection
    $filePath = $uriPath === '/' ? '/index.html' : $uriPath;
    $fullPath = __DIR__ . '/public' . $filePath;

    if (file_exists($fullPath) && is_file($fullPath)) {
        $mimeType = mime_content_type($fullPath);
        $content = file_get_contents($fullPath);
        return new Response(
            200,
            ['Content-Type' => $mimeType ?: 'text/plain'],
            $content
        );
    }

    // Default case: Respond with a 404 error if the file is not found
    return new Response(
        404,
        ['Content-Type' => 'text/plain'],
        "Page not found\n"
    );
});

$socket = new SocketServer('0.0.0.0:8080', [], $loop);
$server->listen($socket);

echo "Server running at http://0.0.0.0:8080\n";

$loop->run();
