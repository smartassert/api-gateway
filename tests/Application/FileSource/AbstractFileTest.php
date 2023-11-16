<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractFileTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUnauthorizedUser(?string $token, string $method): void
    {
        $response = $this->applicationClient->makeFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml',
            $method,
            'content',
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'POST, no token' => [
                'token' => null,
                'method' => 'POST',
            ],
            'POST, empty token' => [
                'token' => '',
                'method' => 'POST',
            ],
            'POST, non-empty invalid token' => [
                'token' => md5((string) rand()),
                'method' => 'POST',
            ],
            'GET, no token' => [
                'token' => null,
                'method' => 'GET',
            ],
            'GET, empty token' => [
                'token' => '',
                'method' => 'GET',
            ],
            'GET, non-empty invalid token' => [
                'token' => md5((string) rand()),
                'method' => 'GET',
            ],
            'DELETE, no token' => [
                'token' => null,
                'method' => 'DELETE',
            ],
            'DELETE, empty token' => [
                'token' => '',
                'method' => 'DELETE',
            ],
            'DELETE, non-empty invalid token' => [
                'token' => md5((string) rand()),
                'method' => 'DELETE',
            ],
        ];
    }

    public function testReadSourceNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
            'GET'
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testReadFileNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            md5((string) rand()) . '.yaml',
            'GET'
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRemoveSourceNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
            'DELETE',
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRemoveFileNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            md5((string) rand()) . '.yaml',
            'DELETE',
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testAddDuplicateFilename(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            $filename,
            'POST',
            $content
        );

        $failedCreateResponse = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            $filename,
            'POST',
            $content
        );

        self::assertSame(400, $failedCreateResponse->getStatusCode());
        self::assertSame('application/json', $failedCreateResponse->getHeaderLine('content-type'));

        self::assertSame(
            [
                'type' => 'duplicate-file-path',
                'context' => [
                    'service' => 'sources',
                    'path' => $filename,
                ],
            ],
            json_decode($failedCreateResponse->getBody()->getContents(), true)
        );
    }

    public function testAddReadRemoveSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createFileSourceResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        $createFileSourceResponseData = json_decode($createFileSourceResponse->getBody()->getContents(), true);
        \assert(is_array($createFileSourceResponseData));

        $sourceData = $createFileSourceResponseData['file_source'] ?? [];
        \assert(is_array($sourceData));
        $sourceId = $sourceData['id'] ?? null;
        \assert(is_string($sourceId));

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $createResponse = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            $filename,
            'POST',
            $content
        );

        self::assertSame(200, $createResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeFileSourceFileRequest($apiKey->key, $sourceId, $filename, 'GET');

        self::assertSame(200, $readResponse->getStatusCode());
        self::assertSame('application/yaml', $readResponse->getHeaderLine('content-type'));
        self::assertSame($content, $readResponse->getBody()->getContents());

        $removeResponse = $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $sourceId,
            $filename,
            'DELETE'
        );
        self::assertSame(200, $removeResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeFileSourceFileRequest($apiKey->key, $sourceId, $filename, 'GET');
        self::assertSame(404, $readResponse->getStatusCode());
    }
}
