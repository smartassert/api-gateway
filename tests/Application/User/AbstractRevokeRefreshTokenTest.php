<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

abstract class AbstractRevokeRefreshTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider badMethodDataProvider
     */
    public function testRevokeRefreshTokenBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeRevokeRefreshTokenRequest(
            'primary_admin_token',
            md5((string) rand()),
            $method
        );

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
    public function testRevokeRefreshTokenUnauthorizedUser(?string $adminToken): void
    {
        $response = self::$staticApplicationClient->makeRevokeRefreshTokenRequest(
            $adminToken,
            md5((string) rand()),
        );

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no admin token' => [
                'adminToken' => null,
            ],
            'empty admin token' => [
                'adminToken' => '',
            ],
            'invalid admin token' => [
                'adminToken' => md5((string) rand()),
            ],
        ];
    }

    public function testRevokeRefreshTokenInvalidUserId(): void
    {
        $response = self::$staticApplicationClient->makeRevokeRefreshTokenRequest(
            'primary_admin_token',
            md5((string) rand()),
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testRevokeRefreshTokenSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $refreshResponse = self::$staticApplicationClient->makeRefreshUserTokenRequest($frontendToken->refreshToken);

        self::assertSame(200, $refreshResponse->getStatusCode());
        self::assertSame('application/json', $refreshResponse->getHeaderLine('content-type'));

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $revokeResponse = self::$staticApplicationClient->makeRevokeRefreshTokenRequest(
            'primary_admin_token',
            $user->id
        );
        self::assertSame(200, $revokeResponse->getStatusCode());

        $refreshResponse = self::$staticApplicationClient->makeRefreshUserTokenRequest($frontendToken->refreshToken);
        self::assertSame(401, $refreshResponse->getStatusCode());
    }
}
