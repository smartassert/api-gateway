<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use AssertBadRequestTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateFileSourceRequest($token, md5((string) rand()));

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, null);

        $this->assertBadRequest($response, 'sources', 'label');
    }

    public function testCreateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);

        $this->assertRetrievedFileSource($response, $label);
    }
}
