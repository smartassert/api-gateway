<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;

abstract class AbstractCreateUserTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertUserResponseTrait;

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateUserBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateUserRequest(
            'primary_admin_token',
            'user@example.com',
            'password',
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
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
    public function testCreateUserUnauthorizedUser(?string $adminToken): void
    {
        $response = $this->applicationClient->makeCreateUserRequest(
            $adminToken,
            md5((string) rand()),
            md5((string) rand())
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateUserUserAlreadyExists(): void
    {
        $userIdentifier = 'user@example.com';
        $password = 'password';

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);
        self::assertSame(200, $createTokenResponse->getStatusCode());

        $response = $this->applicationClient->makeCreateUserRequest('primary_admin_token', $userIdentifier, $password);
        $this->assertUserResponse($response, 409, $userIdentifier);
    }

    public function testCreateUserSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        self::assertSame(401, $createTokenResponse->getStatusCode());

        $response = $this->applicationClient->makeCreateUserRequest('primary_admin_token', $userIdentifier, $password);
        $this->assertUserResponse($response, 200, $userIdentifier);

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);
        self::assertSame(200, $createTokenResponse->getStatusCode());
    }
}
