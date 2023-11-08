<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use Symfony\Component\HttpFoundation\Response;

class InvalidResponseTypeExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof InvalidResponseTypeException) {
            return null;
        }

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
}
