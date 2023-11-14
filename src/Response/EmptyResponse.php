<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EmptyResponse extends SymfonyResponse
{
    public function __construct(int $status = 200)
    {
        parent::__construct(null, $status, ['content-type' => null]);
    }
}
