<?php

declare(strict_types=1);

namespace App\Exception;

class UndefinedServiceException extends \Exception
{
    public function __construct(
        public readonly string $name,
        public readonly string $action,
    ) {
        parent::__construct('Service "' . $name . '" has not been defined.');
    }
}
