<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionApiListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (1 !== preg_match('/^\/api\//', $event->getRequest()->getRequestUri())) {

            return false;
        }

        $exception = $event->getException();
        switch (true) {
            case $exception instanceof MethodNotAllowedHttpException:
                $this->logger->error('Method not allowed', ['exception' => $exception->getMessage()]);
                $response = new JsonResponse(['status' => 'err', 'data' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
                break;

            case $exception instanceof AccessDeniedHttpException:
                $this->logger->error('Access denied', ['exception' => $exception->getMessage()]);
                $response = new JsonResponse(['status' => 'err', 'data' => 'Access denied'], Response::HTTP_FORBIDDEN);
                break;

            case $exception instanceof NotFoundHttpException:
                $this->logger->error('Route not found', ['exception' => $exception->getMessage()]);
                $response = new JsonResponse(['status' => 'err', 'data' => 'Route not found'], Response::HTTP_NOT_FOUND);
                break;

            case $exception instanceof BadRequestHttpException:
                $this->logger->error('Invalid parameter', ['exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
                $response = new JsonResponse(['status' => 'err', 'data' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
                break;

            default:
                $this->logger->error('Internal server error', ['exception' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
                $response = new JsonResponse(['status' => 'err', 'data' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);

        }

        $event->setResponse($response);
    }
}