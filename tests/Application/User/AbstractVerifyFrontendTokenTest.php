<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

abstract class AbstractVerifyFrontendTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testVerifyBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeVerifyUserFrontendTokenRequest('token', $method);

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
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
    public function testVerifyUnauthorizedUser(?string $token): void
    {
        $response = self::$staticApplicationClient->makeVerifyUserFrontendTokenRequest($token);

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

    public function testVerifySuccess(): void
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

        $verifyResponse = self::$staticApplicationClient->makeVerifyUserFrontendTokenRequest($token);

        self::assertSame(200, $verifyResponse->getStatusCode());
        self::assertSame('application/json', $verifyResponse->getHeaderLine('content-type'));

        $verifyResponseData = json_decode($verifyResponse->getBody()->getContents(), true);
        self::assertIsArray($verifyResponseData);
        self::assertArrayHasKey('user', $verifyResponseData);

        $userData = $verifyResponseData['user'];
        self::assertIsArray($userData);

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        self::assertSame($user->id, $userData['id']);
        self::assertSame($user->userIdentifier, $userData['user_identifier']);
    }
}
