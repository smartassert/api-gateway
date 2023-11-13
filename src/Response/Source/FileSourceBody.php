<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\BodyInterface;
use SmartAssert\SourcesClient\Model\FileSource as SourcesClientFileSource;

readonly class FileSourceBody implements BodyInterface
{
    public function __construct(
        private SourcesClientFileSource $source,
    ) {
    }

    /**
     * @return array{id: non-empty-string, label: non-empty-string, type: 'file', deleted_at?: int}
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->source->getId(),
            'label' => $this->source->getLabel(),
            'type' => 'file',
        ];

        $deletedAt = $this->source->getDeletedAt();
        if (null !== $deletedAt) {
            $data['deleted_at'] = $deletedAt;
        }

        return $data;
    }
}
