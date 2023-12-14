<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use CreateGitSourceDataProviderTrait;
    use CreateUpdateGitSourceBadRequestDataProviderTrait;
    use AssertGitSourceTrait;
    use AssertBadRequestTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
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
     * @dataProvider createUpdateGitSourceBadRequestDataProvider
     *
     * @param array<mixed> $expectedInvalidFieldData
     */
    public function testCreateBadRequest(
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        array $expectedInvalidFieldData
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $credentials = null;

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey['key'],
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertBadRequestFoo($response, 'empty', $expectedInvalidFieldData);
    }

    /**
     * @dataProvider createGitSourceDataProvider
     */
    public function testCreateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

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
