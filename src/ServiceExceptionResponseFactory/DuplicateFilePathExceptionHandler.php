<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use SmartAssert\SourcesClient\Exception\DuplicateFilePathException;
use Symfony\Component\HttpFoundation\Response;

class DuplicateFilePathExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof DuplicateFilePathException) {
            return null;
        }

        return new ErrorResponse(
            'duplicate-file-path',
            400,
            [
                'service' => $serviceException->serviceName,
                'path' => $previous->path,
            ],
        );
    }
}
