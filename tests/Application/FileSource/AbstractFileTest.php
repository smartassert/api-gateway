<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
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
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
            'GET'
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testReadFileNotFound(): void
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

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            $sourceId,
            md5((string) rand()) . '.yaml',
            'GET'
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRemoveSourceNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
            'DELETE',
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

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            $sourceId,
            md5((string) rand()) . '.yaml',
            'DELETE',
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testAddReadRemoveSuccess(): void
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

        $createResponse = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            $sourceId,
            $filename,
            'POST',
            $content
        );

        self::assertSame(200, $createResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeFileSourceFileRequest($apiToken, $sourceId, $filename, 'GET');

        self::assertSame(200, $readResponse->getStatusCode());
        self::assertSame('application/yaml', $readResponse->getHeaderLine('content-type'));
        self::assertSame($content, $readResponse->getBody()->getContents());

        $removeResponse = $this->applicationClient->makeFileSourceFileRequest(
            $apiToken,
            $sourceId,
            $filename,
            'DELETE'
        );
        self::assertSame(200, $removeResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeFileSourceFileRequest($apiToken, $sourceId, $filename, 'GET');
        self::assertSame(404, $readResponse->getStatusCode());
    }
}
