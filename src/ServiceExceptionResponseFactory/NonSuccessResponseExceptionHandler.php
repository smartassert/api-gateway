<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
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
            return new ErrorResponse('not-found', 404);
        }

        return new ErrorResponse(
            'non-successful-service-response',
            500,
            [
                'service' => $serviceException->serviceName,
                'status' => $previous->getStatusCode(),
                'message' => $previous->getMessage(),
            ]
        );
    }
}
