<?php

declare(strict_types=1);

namespace App\Response;

class ErrorResponse extends Response
{
    private const DEFAULT_ERROR_CODE = 500;

    public function __construct(
        ErrorResponseBodyInterface $body,
        int $status = self::DEFAULT_ERROR_CODE,
    ) {
        parent::__construct($body, $status);
    }
}
