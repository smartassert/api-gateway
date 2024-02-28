<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\CreateSuiteTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;
    use AssertSuiteTrait;
    use CreateSourceTrait;
    use CreateSuiteTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $response = $this->applicationClient->makeUpdateSuiteRequest(
            $token,
            $suiteId,
            md5((string) rand()),
            md5((string) rand()),
            []
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testUpdateSuiteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $sourceId = (string) new Ulid();
        \assert('' !== $sourceId);

        $label = md5((string) rand());
        $tests = [];

        $response = $this->applicationClient->makeUpdateSuiteRequest(
            $apiKey['key'],
            $suiteId,
            $sourceId,
            $label,
            $tests,
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function testUpdateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, md5((string) rand()), []);

        $updateResponse = $this->applicationClient->makeUpdateSuiteRequest(
            $apiKey['key'],
            $suiteId,
            $sourceId,
            '',
            [],
        );

        $this->assertBadRequest(
            $updateResponse,
            'wrong_size',
            [
                'name' => 'label',
                'value' => '',
                'requirements' => [
                    'data_type' => 'string',
                    'size' => ['minimum' => 1, 'maximum' => 255],
                ],
            ]
        );
    }

    public function testUpdateDeletedSuite(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, md5((string) rand()), []);
        $this->applicationClient->makeDeleteSuiteRequest($apiKey['key'], $suiteId);

        $updateResponse = $this->applicationClient->makeUpdateSuiteRequest(
            $apiKey['key'],
            $suiteId,
            $sourceId,
            md5((string) rand()),
            [],
        );

        self::assertSame(405, $updateResponse->getStatusCode());
    }

    public function testCreateDuplicateLabel(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));

        $this->createSuite($apiKey['key'], $sourceId, $label, ['test1.yaml']);
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, md5((string) rand()), ['test2.yaml']);

        $updateResponse = $this->applicationClient->makeUpdateSuiteRequest(
            $apiKey['key'],
            $suiteId,
            $sourceId,
            $label,
            []
        );

        $this->assertDuplicateObjectResponse($updateResponse, 'label', $label);
    }

    /**
     * @dataProvider updateSuiteDataProvider
     *
     * @param non-empty-string[] $originalTests
     * @param non-empty-string[] $newTests
     */
    public function testUpdateSuccess(
        string $originalLabel,
        array $originalTests,
        string $newLabel,
        array $newTests,
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, $originalLabel, $originalTests);

        $retrievedSuite = $this->applicationClient->makeGetSuiteRequest($apiKey['key'], $suiteId);
        $this->assertRetrievedSuite($retrievedSuite, $sourceId, $originalLabel, $originalTests);

        $updatedSuite = $this->applicationClient->makeUpdateSuiteRequest(
            $apiKey['key'],
            $suiteId,
            $sourceId,
            $newLabel,
            $newTests,
        );
        $this->assertRetrievedSuite($updatedSuite, $sourceId, $newLabel, $newTests);
    }

    /**
     * @return array<mixed>
     */
    public function updateSuiteDataProvider(): array
    {
        return [
            'no tests => no tests' => [
                'originalLabel' => md5((string) rand()),
                'originalTests' => [],
                'newLabel' => md5((string) rand()),
                'newTests' => [],
            ],
            'no tests => has tests' => [
                'originalLabel' => md5((string) rand()),
                'originalTests' => [],
                'newLabel' => md5((string) rand()),
                'newTests' => [
                    md5((string) rand()) . '.yaml',
                    md5((string) rand()) . '.yml',
                ],
            ],
            'has tests => has tests' => [
                'originalLabel' => md5((string) rand()),
                'originalTests' => [
                    md5((string) rand()) . '.yaml',
                    md5((string) rand()) . '.yml',
                ],
                'newLabel' => md5((string) rand()),
                'newTests' => [
                    md5((string) rand()) . '.yaml',
                    md5((string) rand()) . '.yml',
                ],
            ],
            'has tests => no tests' => [
                'originalLabel' => md5((string) rand()),
                'originalTests' => [
                    md5((string) rand()) . '.yaml',
                    md5((string) rand()) . '.yml',
                ],
                'newLabel' => md5((string) rand()),
                'newTests' => [],
            ],
        ];
    }
}
