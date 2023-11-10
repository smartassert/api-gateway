<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\BodyInterface;

readonly class FileSource implements BodyInterface
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     */
    public function __construct(
        private string $id,
        private string $label,
        private ?int $deletedAt,
    ) {
    }

    /**
     * @return array{id: non-empty-string, label: non-empty-string, type: 'file', deleted_at?: int}
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'label' => $this->label,
            'type' => 'file',
        ];

        if (null !== $this->deletedAt) {
            $data['deleted_at'] = $this->deletedAt;
        }

        return $data;
    }
}
