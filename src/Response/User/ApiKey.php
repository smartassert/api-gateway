<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Response\BodyInterface;

readonly class ApiKey implements BodyInterface
{
    /**
     * @param ?non-empty-string $label
     * @param non-empty-string  $key
     */
    public function __construct(
        private ?string $label,
        private string $key,
    ) {
    }

    /**
     * @return array{label: ?non-empty-string, key: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'key' => $this->key,
        ];
    }
}
