<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractRemoveFileTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testRemoveUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeRemoveFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'empty token' => [
                'token' => '',
            ],
            'non-empty invalid token' => [
                'token' => md5((string) rand()),
            ],
        ];
    }

    public function testRemoveSourceNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeRemoveFileSourceFileRequest(
            $apiToken,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRemoveFileNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');
        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiToken, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $response = $this->applicationClient->makeRemoveFileSourceFileRequest(
            $apiToken,
            $sourceId,
            md5((string) rand()) . '.yaml',
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRemoveSuccess(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');
        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiToken, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $this->applicationClient->makeAddFileSourceFileRequest($apiToken, $sourceId, $filename, $content);

        $readResponse = $this->applicationClient->makeReadFileSourceFileRequest($apiToken, $sourceId, $filename);
        self::assertSame(200, $readResponse->getStatusCode());

        $removeResponse = $this->applicationClient->makeRemoveFileSourceFileRequest($apiToken, $sourceId, $filename);
        self::assertSame(200, $removeResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeReadFileSourceFileRequest($apiToken, $sourceId, $filename);
        self::assertSame(404, $readResponse->getStatusCode());
    }
}
