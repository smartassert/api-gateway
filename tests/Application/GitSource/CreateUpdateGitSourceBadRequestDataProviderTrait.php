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
                'expectedInvalidFieldData' => [
                    'name' => 'label',
                    'value' => '',
                    'requirements' => [
                        'data_type' => 'string',
                        'size' => ['minimum' => 1, 'maximum' => 255],
                    ],
                ],
            ],
            'host url missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => null,
                'path' => md5((string) rand()),
                'expectedInvalidFieldData' => [
                    'name' => 'host-url',
                    'value' => '',
                    'requirements' => [
                        'data_type' => 'string',
                        'size' => ['minimum' => 1, 'maximum' => 255],
                    ],
                ],
            ],
            'path missing' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => null,
                'expectedInvalidFieldData' => [
                    'name' => 'path',
                    'value' => '',
                    'requirements' => [
                        'data_type' => 'string',
                        'size' => ['minimum' => 1, 'maximum' => 255],
                    ],
                ],
            ],
        ];
    }
}
