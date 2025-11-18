<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

trait CreateGitSourceDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createGitSourceDataProvider(): array
    {
        return [
            'without credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
            ],
            'with credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => md5((string) rand()),
            ],
        ];
    }
}
