<?php

declare(strict_types=1);

namespace App\Security;

readonly class AuthenticationToken
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public string $token,
    ) {
    }
}
