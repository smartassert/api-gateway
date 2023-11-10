<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractReadFileTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function tesReadBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            'token',
            (string) new Ulid(),
            'filename.yaml',
            $method,
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testReadUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeReadFileSourceFileRequest(
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

    public function testReadSourceNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            $apiToken,
            (string) new Ulid(),
            md5((string) rand()) . '.yaml',
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

        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            $apiToken,
            $sourceId,
            md5((string) rand()) . '.yaml',
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testReadSuccess(): void
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

        $response = $this->applicationClient->makeReadFileSourceFileRequest($apiToken, $sourceId, $filename);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/yaml', $response->getHeaderLine('content-type'));
        self::assertSame($content, $response->getBody()->getContents());
    }
}
