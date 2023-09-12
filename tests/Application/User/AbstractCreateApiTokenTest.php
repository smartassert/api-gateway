<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;

abstract class AbstractCreateApiTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeCreateUserApiTokenRequest(md5((string) rand()), $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $apiKey): void
    {
        $response = self::$staticApplicationClient->makeCreateUserApiTokenRequest($apiKey);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no api key' => [
                'apiKey' => null,
            ],
            'empty api key' => [
                'apiKey' => '',
            ],
            'non-empty invalid api key' => [
                'apiKey' => md5((string) rand()),
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $createApiTokenResponse = self::$staticApplicationClient->makeCreateUserApiTokenRequest($apiKey->key);
        self::assertSame(200, $createApiTokenResponse->getStatusCode());
        self::assertSame('application/json', $createApiTokenResponse->getHeaderLine('content-type'));

        $createApiTokenResponseData = json_decode($createApiTokenResponse->getBody()->getContents(), true);
        self::assertIsArray($createApiTokenResponseData);

        $tokenData = $createApiTokenResponseData['token'];
        self::assertIsArray($tokenData);
        self::assertArrayHasKey('token', $tokenData);

        $apiToken = $tokenData['token'];
        self::assertIsString($apiToken);
        self::assertNotEmpty($apiToken);
    }
}
