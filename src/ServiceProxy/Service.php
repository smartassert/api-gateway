<?php

declare(strict_types=1);

namespace App\ServiceProxy;

readonly class Service
{
    public function __construct(
        private string $name,
        private string $baseUrl,
    ) {
    }

    public function is(string $name): bool
    {
        return $this->name === $name;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
