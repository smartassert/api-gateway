<?php

declare(strict_types=1);

namespace App\Response;

class UnauthorizedResponse extends ErrorResponse
{
    public function __construct()
    {
        parent::__construct('unauthorized', 401);
    }
}
