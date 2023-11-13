<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use CreateGitSourceDataProviderTrait;
    use AssertGitSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeGitSourceRequest($token, 'PUT', (string) new Ulid());

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
        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $apiToken = $apiTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeGitSourceRequest($apiToken, 'PUT', (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider updateGitSourceDataProvider
     */
    public function testUpdateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        string $newLabel,
        string $newHostUrl,
        string $newPath,
        ?string $newCredentials,
    ): void {
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

        $updateResponse = $this->applicationClient->makeGitSourceRequest(
            $apiToken,
            'PUT',
            $id,
            $newLabel,
            $newHostUrl,
            $newPath,
            $newCredentials
        );
        self::assertSame(200, $updateResponse->getStatusCode());
        $this->assertRetrievedGitSource(
            $updateResponse,
            $newLabel,
            $id,
            $newHostUrl,
            $newPath,
            is_string($newCredentials)
        );
    }

    /**
     * @return array<mixed>
     */
    public function updateGitSourceDataProvider(): array
    {
        return [
            'without credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'newLabel' => md5((string) rand()),
                'newHostUrl' => md5((string) rand()),
                'newPath' => md5((string) rand()),
                'newCredentials' => null,
            ],
            'without credentials -> with credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'newLabel' => md5((string) rand()),
                'newHostUrl' => md5((string) rand()),
                'newPath' => md5((string) rand()),
                'newCredentials' => md5((string) rand()),
            ],
            'with credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => md5((string) rand()),
                'newLabel' => md5((string) rand()),
                'newHostUrl' => md5((string) rand()),
                'newPath' => md5((string) rand()),
                'newCredentials' => md5((string) rand()),
            ],
            'with credentials -> without credentials' => [
                'label' => md5((string) rand()),
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => md5((string) rand()),
                'newLabel' => md5((string) rand()),
                'newHostUrl' => md5((string) rand()),
                'newPath' => md5((string) rand()),
                'newCredentials' => null,
            ],
        ];
    }
}
