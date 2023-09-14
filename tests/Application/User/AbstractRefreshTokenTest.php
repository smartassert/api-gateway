<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractRefreshTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider refreshBadMethodDataProvider
     */
    public function testRefreshBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeRefreshUserTokenRequest(
            md5((string) rand()),
            md5((string) rand()),
            $method
        );

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
    public function testRefreshUnauthorizedUser(?string $token, ?string $refreshToken): void
    {
        $response = self::$staticApplicationClient->makeRefreshUserTokenRequest($token, $refreshToken);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token, no refresh token' => [
                'token' => null,
                'refreshToken' => null,
            ],
            'empty token, no refresh token' => [
                'token' => '',
                'refreshToken' => null,
            ],
            'no token, empty token' => [
                'token' => null,
                'refreshToken' => '',
            ],
            'empty token, empty token' => [
                'token' => '',
                'refreshToken' => '',
            ],
            'non-empty invalid token, non-empty invalid refresh token' => [
                'token' => md5((string) rand()),
                'refreshToken' => md5((string) rand()),
            ],
        ];
    }

    public function testRefreshValidTokenInvalidRefreshToken(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $response = self::$staticApplicationClient->makeRefreshUserTokenRequest(
            $frontendToken->token,
            md5((string) rand())
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testRefreshSuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $response = self::$staticApplicationClient->makeRefreshUserTokenRequest(
            $frontendToken->token,
            $frontendToken->refreshToken
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertArrayHasKey('refreshable_token', $responseData);

        $tokenData = $responseData['refreshable_token'];
        self::assertIsArray($tokenData);
        self::assertArrayHasKey('token', $tokenData);
        self::assertArrayHasKey('refresh_token', $tokenData);
    }
}
