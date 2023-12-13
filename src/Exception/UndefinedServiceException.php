<?php

declare(strict_types=1);

namespace App\Exception;

class UndefinedServiceException extends \Exception
{
    public function __construct(public readonly string $name)
    {
        parent::__construct('Service "' . $name . '" has not been defined.');
    }
}
