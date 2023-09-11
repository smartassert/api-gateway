<?php

declare(strict_types=1);

namespace App\Security;

readonly class UserCredentials
{
    /**
     * @param non-empty-string $username
     * @param non-empty-string $password
     */
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
