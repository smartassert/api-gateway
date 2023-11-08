<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\EmptyAuthenticationTokenException;
use App\Exception\EmptyUserCredentialsException;
use App\Exception\EmptyUserIdException;
use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
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

        if ($throwable instanceof EmptyUserIdException) {
            $response = new Response(null, 400);
        }

        if ($throwable instanceof UnauthorizedException) {
            $response = new ErrorResponse(new ErrorResponseBody('unauthorized'), 401);
        }

        if ($throwable instanceof ServiceException) {
            $response = $this->createResponseFromServiceException($throwable);
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function createResponseFromServiceException(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if ($previous instanceof ClientExceptionInterface) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'service-communication-failure',
                    [
                        'service' => $serviceException->serviceName,
                        'error' => [
                            'code' => $previous->getCode(),
                            'message' => $previous->getMessage(),
                        ],
                    ]
                )
            );
        }

        if ($previous instanceof InvalidResponseDataException) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-data',
                    [
                        'service' => $serviceException->serviceName,
                        'data' => $previous->getResponse()->getBody(),
                        'data-type' => [
                            'expected' => $previous->expected,
                            'actual' => $previous->actual,
                        ],
                    ]
                )
            );
        }

        if ($previous instanceof InvalidResponseTypeException) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-response-type',
                    [
                        'service' => $serviceException->serviceName,
                        'content-type' => [
                            'expected' => $previous->expected,
                            'actual' => $previous->actual,
                        ],
                    ]
                )
            );
        }

        if ($previous instanceof NonSuccessResponseException) {
            if (404 === $previous->getCode()) {
                return new ErrorResponse(
                    new ErrorResponseBody('not-found'),
                    $previous->getStatusCode()
                );
            }

            return new ErrorResponse(
                new ErrorResponseBody(
                    'non-successful-service-response',
                    [
                        'service' => $serviceException->serviceName,
                        'status' => $previous->getStatusCode(),
                        'message' => $previous->getMessage(),
                    ]
                )
            );
        }

        if ($previous instanceof InvalidModelDataException) {
            return new ErrorResponse(
                new ErrorResponseBody(
                    'invalid-model-data',
                    [
                        'service' => $serviceException->serviceName,
                        'data' => $previous->getResponse()->getBody(),
                    ]
                )
            );
        }

        return null;
    }
}
