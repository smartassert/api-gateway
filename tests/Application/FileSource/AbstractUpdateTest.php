<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use AssertFileSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeFileSourceRequest(
            $token,
            'PUT',
            (string) new Ulid(),
            md5((string) rand())
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

    public function testUpdateNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceRequest(
            $apiKey->key,
            'PUT',
            (string) new Ulid(),
            md5((string) rand())
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testUpdateDeletedSource(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $createdSourceData = $createResponseData['file_source'];
        \assert(is_array($createdSourceData));

        $id = $createdSourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        $getResponse = $this->applicationClient->makeFileSourceRequest($apiKey->key, 'GET', $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeFileSourceRequest($apiKey->key, 'DELETE', $id);
        self::assertSame(200, $deleteResponse->getStatusCode());

        $newLabel = md5((string) rand());
        $response = $this->applicationClient->makeFileSourceRequest($apiKey->key, 'PUT', $id, $newLabel);
        self::assertSame(405, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));
        self::assertSame(
            [
                'type' => 'modify-read-only-entity',
                'context' => [
                    'service' => 'sources',
                    'type' => 'source',
                    'id' => $id,
                ],
            ],
            json_decode($response->getBody()->getContents(), true)
        );
    }

    public function testUpdateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $createdSourceData = $createResponseData['file_source'];
        \assert(is_array($createdSourceData));

        $id = $createdSourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        $newLabel = md5((string) rand());

        $response = $this->applicationClient->makeFileSourceRequest($apiKey->key, 'PUT', $id, $newLabel);

        $this->assertRetrievedFileSource($response, $newLabel, $id);
    }
}
