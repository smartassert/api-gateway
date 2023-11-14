<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorResponse extends JsonResponse
{
    /**
     * @param non-empty-string $type
     * @param ?array<mixed>    $context
     */
    public function __construct(
        private readonly string $type,
        int $status,
        private readonly ?array $context = null,
    ) {
        parent::__construct($this->toArray(), $status);
    }

    /**
     * @return array{type: non-empty-string, context?: array<mixed>}
     */
    private function toArray(): array
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
