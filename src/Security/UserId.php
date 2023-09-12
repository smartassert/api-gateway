<?php

declare(strict_types=1);

namespace App\Security;

readonly class UserId
{
    /**
     * @param non-empty-string $id
     */
    public function __construct(
        public string $id,
    ) {
    }
}
