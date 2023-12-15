<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractRefreshTokenTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertRefreshTokenResponseTrait;

    /**
     * @dataProvider refreshBadMethodDataProvider
     */
    public function testRefreshBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeRefreshUserTokenRequest(md5((string) rand()), $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function refreshBadMethodDataProvider(): array
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
    public function testRefreshUnauthorizedUser(?string $refreshToken): void
    {
        $response = $this->applicationClient->makeRefreshUserTokenRequest($refreshToken);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRefreshSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $response = $this->applicationClient->makeRefreshUserTokenRequest($frontendToken['refresh_token']);
        $this->assertRefreshTokenResponse($response);
    }
}
