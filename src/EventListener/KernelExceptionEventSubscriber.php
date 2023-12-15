<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\Response\ErrorResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

readonly class KernelExceptionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<class-string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                ['onKernelException', 100],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $response = null;

        if ($throwable instanceof UndefinedServiceException) {
            $response = new ErrorResponse(
                'undefined-service',
                500,
                [
                    'service' => $throwable->name,
                    'action' => $throwable->action,
                ]
            );
        }

        if ($throwable instanceof ServiceException) {
            $response = new ErrorResponse(
                'service-communication-failure',
                500,
                [
                    'service' => $throwable->serviceName,
                    'error' => [
                        'code' => $throwable->previousException->getCode(),
                        'message' => $throwable->previousException->getMessage(),
                    ],
                ]
            );
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
