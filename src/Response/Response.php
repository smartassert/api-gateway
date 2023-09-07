<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response extends JsonResponse
{
    private const DEFAULT_ERROR_CODE = 200;

    public function __construct(BodyInterface $body, int $status = self::DEFAULT_ERROR_CODE)
    {
        parent::__construct($body->toArray(), $status);
    }
}
