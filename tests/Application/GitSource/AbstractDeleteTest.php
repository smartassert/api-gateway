<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractDeleteTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeDeleteGitSourceRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeDeleteGitSourceRequest($apiKey->key, (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createGitSourceDataProvider
     */
    public function testDeleteSuccess(string $label, string $hostUrl, string $path, ?string $credentials): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $id = $this->createGitSource(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $getResponse = $this->applicationClient->makeReadGitSourceRequest($apiKey->key, $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeDeleteGitSourceRequest($apiKey->key, $id);
        $this->assertDeletedGitSource(
            $deleteResponse,
            $label,
            $id,
            $hostUrl,
            $path,
            is_string($credentials)
        );
    }
}
