<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

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

    public function testCreateBadMethod(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, md5((string) rand()), 'GET');

        self::assertSame(405, $response->getStatusCode());
    }

    public function testCreateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, null);

        $this->assertBadRequestFoo(
            $response,
            'empty',
            [
                'name' => 'label',
                'value' => '',
                'requirements' => [
                    'data_type' => 'string',
                    'size' => ['minimum' => 1, 'maximum' => 255],
                ],
            ]
        );
    }

    public function testCreateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $label = md5((string) rand());

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);

        $this->assertRetrievedFileSource($response, $label, $user->id);
    }
}
