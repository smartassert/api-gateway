<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\EmptyAuthenticationTokenException;
use App\Exception\EmptyUserCredentialsException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class KernelExceptionEventSubscriber implements EventSubscriberInterface
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

        if ($throwable instanceof EmptyAuthenticationTokenException) {
            $response = new Response(null, 401);
        }

        if ($throwable instanceof EmptyUserCredentialsException) {
            $response = new Response(null, 401);
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
