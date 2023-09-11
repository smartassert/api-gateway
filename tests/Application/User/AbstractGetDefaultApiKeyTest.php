<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractGetDefaultApiKeyTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testGetDefaultApiKeyBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeListUserApiKeysRequest('token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
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
    public function testGetDefaultApiKeyUnauthorizedUser(?string $token): void
    {
        $response = self::$staticApplicationClient->makeGetUserDefaultApiKeyRequest($token);

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

    public function testGetDefaultApiKeySuccess(): void
    {
        $createResponse = self::$staticApplicationClient->makeCreateUserFrontendTokenRequest(
            'user@example.com',
            'password'
        );

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));
        \assert(array_key_exists('refreshable_token', $createResponseData));

        $tokenData = $createResponseData['refreshable_token'];
        \assert(is_array($tokenData));

        $token = $tokenData['token'] ?? null;

        $apiKeyResponse = self::$staticApplicationClient->makeGetUserDefaultApiKeyRequest($token);

        self::assertSame(200, $apiKeyResponse->getStatusCode());
        self::assertSame('application/json', $apiKeyResponse->getHeaderLine('content-type'));

        $apiKeyResponseData = json_decode($apiKeyResponse->getBody()->getContents(), true);
        self::assertIsArray($apiKeyResponseData);
        self::assertArrayHasKey('api_key', $apiKeyResponseData);

        $apKeyData = $apiKeyResponseData['api_key'];
        self::assertIsArray($apKeyData);

        self::assertArrayHasKey('label', $apKeyData);
        self::assertNull($apKeyData['label']);

        self::assertArrayHasKey('key', $apKeyData);
        $key = $apKeyData['key'];
        self::assertIsString($key);
        self::assertNotEmpty($key);
    }
}
