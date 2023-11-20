<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

trait CreateSuiteDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createSuiteDataProvider(): array
    {
        return [
            'no tests' => [
                'label' => md5((string) rand()),
                'tests' => [],
            ],
            'has tests' => [
                'label' => md5((string) rand()),
                'tests' => [
                    md5((string) rand()) . '.yaml',
                    md5((string) rand()) . '.yml',
                ],
            ],
        ];
    }
}
