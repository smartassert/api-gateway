<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetTest extends AbstractApplicationTestCase
{
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testGetUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeGitSourceRequest($token, 'GET', (string) new Ulid());

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

    public function testGetNotFound(): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeGitSourceRequest($apiToken, 'GET', (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createGitSourceDataProvider
     */
    public function testGetSuccess(string $label, string $hostUrl, string $path, ?string $credentials): void
    {
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);

        $apiToken = $apiTokenProvider->get('user@example.com');

        $createResponse = $this->applicationClient->makeCreateGitSourceRequest(
            $apiToken,
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

        $getResponse = $this->applicationClient->makeGitSourceRequest($apiToken, 'GET', $id);
        self::assertSame(200, $getResponse->getStatusCode());
        $this->assertRetrievedGitSource(
            $getResponse,
            $label,
            $id,
            $hostUrl,
            $path,
            is_string($credentials)
        );
    }
}
