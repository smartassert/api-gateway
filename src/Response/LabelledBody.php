<?php

declare(strict_types=1);

namespace App\Response;

readonly class LabelledBody implements BodyInterface
{
    /**
     * @param non-empty-string $identifier
     */
    public function __construct(
        private string $identifier,
        private BodyInterface $body,
    ) {
    }

    /**
     * @return array<non-empty-string,array<mixed>>
     */
    public function toArray(): array
    {
        return [
            $this->identifier => $this->body->toArray(),
        ];
    }
}
