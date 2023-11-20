<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testGetUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeReadFileSourceRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeReadFileSourceRequest($apiKey->key, (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    public function testGetSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey->key, $label);

        $response = $this->applicationClient->makeReadFileSourceRequest($apiKey->key, $id);

        $this->assertRetrievedFileSource($response, $label, $id);
    }
}
