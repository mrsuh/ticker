#!/usr/bin/env php
<?php

use App\Kernel;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;

require __DIR__ . '/../config/bootstrap.php';
$env   = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env));
if ($debug) {
    umask(0000);
    Debug::enable();
}
if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}
if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}
$loop   = React\EventLoop\Factory::create();
$kernel = new Kernel($env, $debug);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);
$cmd = $application->find('doctrine:schema:update');
$cmd->run(new ArrayInput(['--force' => true]), new NullOutput());

/** @var \Psr\Log\LoggerInterface $logger */
$logger = $kernel->getContainer()->get('logger');
/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = $kernel->getContainer()->get('entityManager');
/** @var \App\Model\TickerModel */
$tickerModel = $kernel->getContainer()->get('tickerModel');

$webroot = $kernel->getContainer()->getParameter('kernel.project_dir') . '/public';

$httpHandler = function (ServerRequestInterface $request) use ($kernel, $logger, $entityManager) {
    try {
        $method  = $request->getMethod();
        $headers = $request->getHeaders();
        $content = $request->getBody()->getContents();
        $post    = [];

        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']) && is_array($request->getParsedBody())
        ) {
            $post = $request->getParsedBody();
        }

        $sfRequest = new Request(
            $request->getQueryParams(),
            $post,
            [],
            $request->getCookieParams(),
            $request->getUploadedFiles(),
            [],
            $content
        );

        $sfRequest->setMethod($method);
        $sfRequest->headers->replace($headers);
        $sfRequest->server->set('REQUEST_URI', (string)$request->getUri());
        if (isset($headers['Host'])) {
            $sfRequest->server->set('SERVER_NAME', current($headers['Host']));
        }

        $entityManager->clear();

        $sfResponse = $kernel->handle($sfRequest);

        $kernel->terminate($sfRequest, $sfResponse);
        $kernel->reboot(null);
    } catch (\Throwable $e) {
        $logger->error('Internal server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        $sfResponse = new SymfonyResponse('Internal server error', 500);
    }

    return new ReactResponse(
        $sfResponse->getStatusCode(),
        $sfResponse->headers->all(),
        $sfResponse->getContent()
    );
};

$server = new HttpServer([new WebrootPreloadMiddleware($webroot, $logger), $httpHandler]);

$server->on('error', function (\Exception $e) use ($logger) {
    $logger->error('Internal server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
);
$socket = new SocketServer('tcp://0.0.0.0:9090', $loop);
$server->listen($socket);
$logger->info('Server running', ['addr' => 'tcp://0.0.0.0:9090']);

$tickerModel->sync();
$loop->addPeriodicTimer(5, function () use ($logger, $tickerModel) {
    try {
        $tickerModel->sync();
    } catch (\Throwable $e) {
        $logger->error('Internal server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
}
);

$loop->run();