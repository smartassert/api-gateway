<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use App\Tests\Services\ApplicationClient\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateGitSourceDataProviderTrait;
    use CreateUpdateGitSourceBadRequestDataProviderTrait;
    use AssertGitSourceTrait;
    use AssertBadRequestTrait;

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $token,
            'label',
            'hostUrl',
            'path',
            'credentials'
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @param array<mixed> $expectedInvalidParameterData
     */
    #[DataProvider('createUpdateGitSourceBadRequestDataProvider')]
    public function testCreateBadRequest(
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        array $expectedInvalidParameterData
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $credentials = null;

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey['key'],
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertBadRequest($response, 'wrong_size', $expectedInvalidParameterData);
    }

    /**
     * @param callable(Client, string, string): void $existingSourceCreator
     */
    #[DataProvider('createDuplicateLabelDataProvider')]
    public function testCreateDuplicateLabel(callable $existingSourceCreator): void
    {
        $label = md5((string) rand());

        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $existingSourceCreator($this->applicationClient, $apiKey['key'], $label);

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey['key'],
            $label,
            md5((string) rand()),
            md5((string) rand()),
            null
        );

        $this->assertDuplicateObjectResponse($response, 'label', $label);
    }

    /**
     * @return array<mixed>
     */
    public static function createDuplicateLabelDataProvider(): array
    {
        return [
            'file source has label' => [
                'existingSourceCreator' => function (Client $applicationClient, string $apiKey, string $label): void {
                    $response = $applicationClient->makeCreateFileSourceRequest($apiKey, $label);
                    self::assertSame(200, $response->getStatusCode());
                },
            ],
            'git source has label' => [
                'existingSourceCreator' => function (Client $applicationClient, string $apiKey, string $label): void {
                    $response = $applicationClient->makeCreateGitSourceRequest(
                        $apiKey,
                        $label,
                        md5((string) rand()),
                        md5((string) rand()),
                        null
                    );
                    self::assertSame(200, $response->getStatusCode());
                },
            ],
        ];
    }

    #[DataProvider('createGitSourceDataProvider')]
    public function testCreateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey['key'],
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertRetrievedGitSource(
            $response,
            $label,
            null,
            $hostUrl,
            $path,
            is_string($credentials),
            $user['id']
        );
    }
}
