<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractGetDefaultApiKeyTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;

    #[DataProvider('badMethodDataProvider')]
    public function testGetDefaultApiKeyBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeGetUserDefaultApiKeyRequest('token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public static function badMethodDataProvider(): array
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

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testGetDefaultApiKeyUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeGetUserDefaultApiKeyRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetDefaultApiKeySuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user1@example.com');

        $apiKeyResponse = $this->applicationClient->makeGetUserDefaultApiKeyRequest($frontendToken['token']);

        self::assertSame(200, $apiKeyResponse->getStatusCode());
        self::assertSame('application/json', $apiKeyResponse->getHeaderLine('content-type'));

        $apiKeyResponseData = json_decode($apiKeyResponse->getBody()->getContents(), true);
        self::assertIsArray($apiKeyResponseData);

        self::assertArrayHasKey('label', $apiKeyResponseData);
        self::assertNull($apiKeyResponseData['label']);

        self::assertArrayHasKey('key', $apiKeyResponseData);
        $key = $apiKeyResponseData['key'];
        self::assertIsString($key);
        self::assertNotEmpty($key);
    }
}
