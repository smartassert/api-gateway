<?php

declare(strict_types=1);

namespace App\Response;

interface ErrorResponseBodyInterface extends BodyInterface
{
    /**
     * @return non-empty-string
     */
    public function getType(): string;

    /**
     * @return ?array<mixed>
     */
    public function getContext(): ?array;

    /**
     * @return array{type: non-empty-string, context?: array<mixed>}
     */
    public function toArray(): array;
}
