<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
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
            new ErrorResponseBody(
                'invalid-model-data',
                [
                    'service' => $serviceException->serviceName,
                    'data' => $previous->getResponse()->getBody(),
                ]
            )
        );
    }
}
