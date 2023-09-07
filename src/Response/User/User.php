<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Response\BodyInterface;

readonly class User implements BodyInterface
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $userIdentifier
     */
    public function __construct(
        private string $id,
        private string $userIdentifier,
    ) {
    }

    /**
     * @return array{id: non-empty-string, user_identifier: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_identifier' => $this->userIdentifier,
        ];
    }
}
