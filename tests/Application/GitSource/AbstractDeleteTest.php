<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractDeleteTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

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

        $createResponse = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $createdSourceData = $createResponseData['git_source'];
        \assert(is_array($createdSourceData));

        $id = $createdSourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

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
