<?php

declare(strict_types=1);

namespace App\Security;

readonly class RefreshToken
{
    /**
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        public string $refreshToken,
    ) {
    }
}
