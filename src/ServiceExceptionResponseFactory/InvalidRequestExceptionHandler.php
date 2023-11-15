<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use SmartAssert\SourcesClient\Exception\InvalidRequestException;
use SmartAssert\SourcesClient\Model\InvalidRequestField;
use Symfony\Component\HttpFoundation\Response;

class InvalidRequestExceptionHandler implements HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response
    {
        $previous = $serviceException->previousException;

        if (!$previous instanceof InvalidRequestException) {
            return null;
        }

        $invalidFieldData = [];
        $invalidRequestField = $previous->getInvalidRequestField();
        if ($invalidRequestField instanceof InvalidRequestField) {
            $invalidFieldData = [
                'name' => $invalidRequestField->name,
                'value' => $invalidRequestField->value,
                'message' => $invalidRequestField->message,
            ];
        }

        return new ErrorResponse(
            'bad-request',
            400,
            [
                'service' => $serviceException->serviceName,
                'invalid-field' => $invalidFieldData,
            ],
        );
    }
}
