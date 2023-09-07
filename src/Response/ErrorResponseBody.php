<?php

declare(strict_types=1);

namespace App\Response;

readonly class ErrorResponseBody implements ErrorResponseBodyInterface
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

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
