<?php

declare(strict_types=1);

namespace App\Response;

readonly class ArrayBody implements BodyInterface
{
    /**
     * @param array<mixed> $body
     */
    public function __construct(private array $body)
    {
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->body;
    }
}
