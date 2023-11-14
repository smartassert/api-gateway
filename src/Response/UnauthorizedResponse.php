<?php

declare(strict_types=1);

namespace App\Response;

class UnauthorizedResponse extends EmptyResponse
{
    public function __construct()
    {
        parent::__construct(401);
    }
}
