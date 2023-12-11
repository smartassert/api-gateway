<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractListApiKeysTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;

    /**
     * @dataProvider badMethodDataProvider
     */
    public function testListBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeListUserApiKeysRequest('token', $method);

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
        $response = $this->applicationClient->makeListUserApiKeysRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testListSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $listResponse = $this->applicationClient->makeListUserApiKeysRequest($frontendToken->token);

        self::assertSame(200, $listResponse->getStatusCode());
        self::assertSame('application/json', $listResponse->getHeaderLine('content-type'));

        $listResponseData = json_decode($listResponse->getBody()->getContents(), true);
        self::assertIsArray($listResponseData);

        $apKeyData = $listResponseData[0];
        self::assertIsArray($apKeyData);

        self::assertArrayHasKey('label', $apKeyData);
        self::assertNull($apKeyData['label']);

        self::assertArrayHasKey('key', $apKeyData);
        $key = $apKeyData['key'];
        self::assertIsString($key);
        self::assertNotEmpty($key);
    }
}
