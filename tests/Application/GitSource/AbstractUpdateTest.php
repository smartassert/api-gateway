<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractUpdateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateGitSourceDataProviderTrait;
    use CreateUpdateGitSourceBadRequestDataProviderTrait;
    use AssertGitSourceTrait;
    use AssertBadRequestTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeUpdateGitSourceRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testUpdateNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeUpdateGitSourceRequest($apiKey['key'], (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createUpdateGitSourceBadRequestDataProvider
     *
     * @param array<mixed> $expectedInvalidFieldData
     */
    public function testUpdateBadRequest(
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        array $expectedInvalidFieldData
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $id = $this->createGitSource(
            $apiKey['key'],
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            null
        );

        $updateResponse = $this->applicationClient->makeUpdateGitSourceRequest(
            $apiKey['key'],
            $id,
            $label,
            $hostUrl,
            $path
        );

        $this->assertBadRequest($updateResponse, 'empty', $expectedInvalidFieldData);
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
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $id = $this->createGitSource(
            $apiKey['key'],
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $updateResponse = $this->applicationClient->makeUpdateGitSourceRequest(
            $apiKey['key'],
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
            is_string($newCredentials),
            $user['id']
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
