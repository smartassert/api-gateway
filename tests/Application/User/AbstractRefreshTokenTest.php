<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractRefreshTokenTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertRefreshTokenResponseTrait;

    #[DataProvider('refreshBadMethodDataProvider')]
    public function testRefreshBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeRefreshUserTokenRequest(md5((string) rand()), $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public static function refreshBadMethodDataProvider(): array
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

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testRefreshUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeRefreshUserTokenRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRefreshSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user1@example.com');

        $response = $this->applicationClient->makeRefreshUserTokenRequest($frontendToken['refresh_token']);
        $this->assertRefreshTokenResponse($response);
    }
}
