<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
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

    /**
     * @dataProvider createUpdateGitSourceBadRequestDataProvider
     */
    public function testCreateBadRequest(
        ?string $label,
        ?string $hostUrl,
        ?string $path,
        string $expectedInvalidField
    ): void {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $credentials = null;

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertBadRequest($response, $expectedInvalidField);
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

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
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
            is_string($credentials)
        );
    }
}
