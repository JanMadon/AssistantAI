<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof BadRequestHttpException) {
            $event->setResponse(new JsonResponse([
                'status' => 'error',
                'errors' => json_decode($exception->getMessage(), true),
            ], JsonResponse::HTTP_BAD_REQUEST));
        }
    }
}
