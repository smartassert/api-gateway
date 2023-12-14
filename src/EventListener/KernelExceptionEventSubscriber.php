<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\EmptyUserIdException;
use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\ServiceExceptionResponseFactory\Factory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

readonly class KernelExceptionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Factory $serviceExceptionResponseFactory,
    ) {
    }

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

        if ($throwable instanceof EmptyUserIdException) {
            $response = new EmptyResponse(400);
        }

        if ($throwable instanceof ServiceException) {
            $response = $this->serviceExceptionResponseFactory->create($throwable);
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
