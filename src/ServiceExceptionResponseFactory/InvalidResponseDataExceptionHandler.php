<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use Symfony\Component\HttpFoundation\Response;

class InvalidResponseDataExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof InvalidResponseDataException) {
            return null;
        }

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
}
