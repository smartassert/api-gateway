<?php

declare(strict_types=1);

namespace App\Exception;

class BareHttpException extends \Exception
{
    public function __construct(int $statusCode)
    {
        parent::__construct('', $statusCode);
    }
}
