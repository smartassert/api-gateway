<?php

declare(strict_types=1);

namespace App\Response;

readonly class ErrorResponseBody
{
    /**
     * @param non-empty-string $type
     * @param ?array<mixed>    $context
     */
    public function __construct(
        private string $type,
        private ?array $context = null,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return ?array<mixed>
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @return array{type: non-empty-string, context?: array<mixed>}
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
        ];

        if (is_array($this->context)) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
