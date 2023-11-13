<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\BodyInterface;
use SmartAssert\SourcesClient\Model\GitSource as SourcesClientGitSource;

readonly class GitSourceBody implements BodyInterface
{
    public function __construct(
        private SourcesClientGitSource $source,
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
            'id' => $this->source->getId(),
            'label' => $this->source->getLabel(),
            'type' => 'git',
            'host_url' => $this->source->getHostUrl(),
            'path' => $this->source->getPath(),
            'has_credentials' => $this->source->hasCredentials(),
        ];

        $deletedAt = $this->source->getDeletedAt();
        if (null !== $deletedAt) {
            $data['deleted_at'] = $deletedAt;
        }

        return $data;
    }
}
