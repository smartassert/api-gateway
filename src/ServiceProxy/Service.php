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

    public function getName(): string
    {
        return $this->name;
    }

    public function createUrl(string $relative): string
    {
        return $this->baseUrl . $relative;
    }
}
