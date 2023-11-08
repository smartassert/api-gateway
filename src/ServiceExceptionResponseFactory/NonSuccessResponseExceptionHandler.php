<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use Symfony\Component\HttpFoundation\Response;

class NonSuccessResponseExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof NonSuccessResponseException) {
            return null;
        }

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
}
