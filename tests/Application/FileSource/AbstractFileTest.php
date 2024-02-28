<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractFileTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateSourceTrait;
    use AssertBadRequestTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml',
            'content'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testReadUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeUpdateFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml',
            'content'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeDeleteFileSourceFileRequest(
            $token,
            (string) new Ulid(),
            'filename.yaml'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testReadSourceNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            $apiKey['key'],
            (string) new Ulid(),
            md5((string) rand()) . '.yaml'
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function testReadFileNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], $label);

        $response = $this->applicationClient->makeReadFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            md5((string) rand()) . '.yaml'
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testRemoveSourceNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeDeleteFileSourceFileRequest(
            $apiKey['key'],
            (string) new Ulid(),
            md5((string) rand()) . '.yaml'
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function testRemoveFileNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], $label);

        $response = $this->applicationClient->makeDeleteFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            md5((string) rand()) . '.yaml'
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testAddDuplicateFilename(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], $label);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $this->applicationClient->makeCreateFileSourceFileRequest($apiKey['key'], $sourceId, $filename, $content);

        $failedCreateResponse = $this->applicationClient->makeCreateFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            $filename,
            $content
        );

        $this->assertDuplicateObjectResponse($failedCreateResponse, 'filename', $filename);
    }

    public function testAddReadUpdateRemoveSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], $label);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $createResponse = $this->applicationClient->makeCreateFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            $filename,
            $content
        );
        self::assertSame(200, $createResponse->getStatusCode());

        $updatedContent = md5((string) rand());
        $updateResponse = $this->applicationClient->makeUpdateFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            $filename,
            $updatedContent
        );
        self::assertSame(200, $updateResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeReadFileSourceFileRequest($apiKey['key'], $sourceId, $filename);

        self::assertSame(200, $readResponse->getStatusCode());
        self::assertSame('text/x-yaml; charset=utf-8', $readResponse->getHeaderLine('content-type'));
        self::assertSame($updatedContent, $readResponse->getBody()->getContents());

        $removeResponse = $this->applicationClient->makeDeleteFileSourceFileRequest(
            $apiKey['key'],
            $sourceId,
            $filename
        );
        self::assertSame(200, $removeResponse->getStatusCode());

        $readResponse = $this->applicationClient->makeReadFileSourceFileRequest($apiKey['key'], $sourceId, $filename);
        self::assertSame(404, $readResponse->getStatusCode());
    }
}
