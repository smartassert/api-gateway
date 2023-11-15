<?php

declare(strict_types=1);

namespace App\Security;

readonly class ApiToken
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public string $token,
    ) {
    }
}
