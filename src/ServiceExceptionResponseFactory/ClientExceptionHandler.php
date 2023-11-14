<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class ClientExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof ClientExceptionInterface) {
            return null;
        }

        return new ErrorResponse(
            'service-communication-failure',
            500,
            [
                'service' => $serviceException->serviceName,
                'error' => [
                    'code' => $previous->getCode(),
                    'message' => $previous->getMessage(),
                ],
            ]
        );
    }
}
