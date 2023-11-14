<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorResponse extends JsonResponse
{
    private const DEFAULT_ERROR_CODE = 500;

    public function __construct(
        ErrorResponseBody $body,
        int $status = self::DEFAULT_ERROR_CODE,
    ) {
        parent::__construct($body, $status);
    }
}
