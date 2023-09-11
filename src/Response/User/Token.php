<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Response\BodyInterface;

readonly class Token implements BodyInterface
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        private string $token,
    ) {
    }

    /**
     * @return array{token: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
