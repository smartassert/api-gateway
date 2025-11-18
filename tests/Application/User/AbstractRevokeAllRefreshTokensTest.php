<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

abstract class AbstractRevokeAllRefreshTokensTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;

    /**
     * @dataProvider badMethodDataProvider
     */
    public function testRevokeRefreshTokenBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            'primary_admin_token',
            md5((string) rand()),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public static function badMethodDataProvider(): array
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
    public function testRevokeRefreshTokenUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            $token,
            md5((string) rand()),
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRevokeRefreshTokenInvalidUserId(): void
    {
        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            'primary_admin_token',
            md5((string) rand()),
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRevokeRefreshTokenSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user1@example.com');

        $refreshResponse = $this->applicationClient->makeRefreshUserTokenRequest($frontendToken['refresh_token']);

        self::assertSame(200, $refreshResponse->getStatusCode());
        self::assertSame('application/json', $refreshResponse->getHeaderLine('content-type'));

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user1@example.com');

        $revokeResponse = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest(
            'primary_admin_token',
            $user['id']
        );
        echo $revokeResponse->getBody()->getContents();

        self::assertSame(200, $revokeResponse->getStatusCode());

        $refreshResponse = $this->applicationClient->makeRefreshUserTokenRequest($frontendToken['refresh_token']);
        self::assertSame(401, $refreshResponse->getStatusCode());
    }
}
