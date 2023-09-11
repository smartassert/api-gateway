<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractListApiKeysTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testListBadMethod(string $method): void
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
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = self::$staticApplicationClient->makeListUserApiKeysRequest($token);

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

    public function testListSuccess(): void
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

        $listResponse = self::$staticApplicationClient->makeListUserApiKeysRequest($token);

        self::assertSame(200, $listResponse->getStatusCode());
        self::assertSame('application/json', $listResponse->getHeaderLine('content-type'));

        $listResponseData = json_decode($listResponse->getBody()->getContents(), true);
        self::assertIsArray($listResponseData);
        self::assertArrayHasKey('api_keys', $listResponseData);

        $apiKeysData = $listResponseData['api_keys'];
        self::assertIsArray($apiKeysData);
        self::assertCount(1, $apiKeysData);

        $apKeyData = $apiKeysData[0];
        self::assertIsArray($apKeyData);

        self::assertArrayHasKey('label', $apKeyData);
        self::assertNull($apKeyData['label']);

        self::assertArrayHasKey('key', $apKeyData);
        $key = $apKeyData['key'];
        self::assertIsString($key);
        self::assertNotEmpty($key);
    }
}
