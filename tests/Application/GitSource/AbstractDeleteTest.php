<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractDeleteTest extends AbstractApplicationTestCase
{
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeGitSourceRequest(
            $token,
            'DELETE',
            (string) new Ulid(),
            'label',
            'hostUrl',
            'path',
            'credentials'
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

    public function testDeleteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeGitSourceRequest($apiKey->key, 'DELETE', (string) new Ulid());

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

        $getResponse = $this->applicationClient->makeGitSourceRequest($apiKey->key, 'GET', $id);
        self::assertSame(200, $getResponse->getStatusCode());

        $deleteResponse = $this->applicationClient->makeGitSourceRequest($apiKey->key, 'DELETE', $id);
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
