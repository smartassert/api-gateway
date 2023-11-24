<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
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
            'invalid-response-data',
            500,
            [
                'service' => $serviceException->serviceName,
                'data' => $previous->getHttpResponse()->getBody()->getContents(),
                'data-type' => [
                    'expected' => $previous->expected,
                    'actual' => $previous->actual,
                ],
            ]
        );
    }
}
