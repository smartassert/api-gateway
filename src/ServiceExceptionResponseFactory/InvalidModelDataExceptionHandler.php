<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use Symfony\Component\HttpFoundation\Response;

class InvalidModelDataExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof InvalidModelDataException) {
            return null;
        }

        return new ErrorResponse(
            'invalid-model-data',
            500,
            [
                'service' => $serviceException->serviceName,
                'data' => $previous->getPayload(),
            ],
        );
    }
}
