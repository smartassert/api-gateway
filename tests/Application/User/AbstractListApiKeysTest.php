<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

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
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $listResponse = self::$staticApplicationClient->makeListUserApiKeysRequest($frontendToken->token);

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
