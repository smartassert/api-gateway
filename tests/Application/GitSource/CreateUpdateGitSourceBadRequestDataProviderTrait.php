<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

trait CreateUpdateGitSourceBadRequestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createUpdateGitSourceBadRequestDataProvider(): array
    {
        return [
            'label missing' => [
                'label' => null,
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'expectedInvalidField' => 'label',
            ],
            'host url missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => null,
                'path' => md5((string) rand()),
                'expectedInvalidField' => 'host-url',
            ],
            'path missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => null,
                'expectedInvalidField' => 'path',
            ],
        ];
    }
}
