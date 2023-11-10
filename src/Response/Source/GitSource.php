<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\BodyInterface;

readonly class GitSource implements BodyInterface
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     * @param non-empty-string $hostUrl
     * @param non-empty-string $path
     */
    public function __construct(
        private string $id,
        private string $label,
        private string $hostUrl,
        private string $path,
        private bool $hasCredentials,
        private ?int $deletedAt,
    ) {
    }

    /**
     * @return array{
     *     id: non-empty-string,
     *     label: non-empty-string,
     *     type: 'git',
     *     host_url: non-empty-string,
     *     path: non-empty-string,
     *     deleted_at?: int
     * }
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'label' => $this->label,
            'type' => 'git',
            'host_url' => $this->hostUrl,
            'path' => $this->path,
            'has_credentials' => $this->hasCredentials,
        ];

        if (null !== $this->deletedAt) {
            $data['deleted_at'] = $this->deletedAt;
        }

        return $data;
    }
}
