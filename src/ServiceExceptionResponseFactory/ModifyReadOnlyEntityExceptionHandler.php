<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use App\Response\ErrorResponseBody;
use SmartAssert\SourcesClient\Exception\ModifyReadOnlyEntityException;
use Symfony\Component\HttpFoundation\Response;

class ModifyReadOnlyEntityExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof ModifyReadOnlyEntityException) {
            return null;
        }

        return new ErrorResponse(
            new ErrorResponseBody(
                'modify-read-only-entity',
                [
                    'service' => $serviceException->serviceName,
                    'type' => $previous->type,
                    'id' => $previous->id,
                ]
            ),
            405
        );
    }
}
