<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use AssertBadRequestTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $token,
            (string) new Ulid(),
            md5((string) rand())
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testUpdateNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeUpdateFileSourceRequest(
            $apiKey->key,
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
        $id = $this->createFileSource($apiKey->key, $label);

        $getResponse = $this->applicationClient->makeGetSourceRequest($apiKey->key, $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeDeleteFileSourceRequest($apiKey->key, $id);
        self::assertSame(200, $deleteResponse->getStatusCode());

        $newLabel = md5((string) rand());
        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, $newLabel);
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

    public function testUpdateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, null);

        $this->assertBadRequest($response, 'sources', 'label');
    }

    public function testUpdateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $newLabel = md5((string) rand());

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey->key, $id, $newLabel);
        $this->assertRetrievedFileSource($response, $newLabel, null, $id);
    }
}
