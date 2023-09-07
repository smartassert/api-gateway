<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Response\BodyInterface;

readonly class RefreshableToken implements BodyInterface
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        private string $token,
        private string $refreshToken,
    ) {
    }

    /**
     * @return array{token: non-empty-string, refresh_token: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'refresh_token' => $this->refreshToken,
        ];
    }
}
