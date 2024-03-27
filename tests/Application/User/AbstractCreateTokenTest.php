<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractCreateTokenTest extends AbstractApplicationTestCase
{
    use AssertRefreshTokenResponseTrait;

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateUserTokenRequest(
            'user1@example.com',
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
    public function testCreateUnauthorizedUser(?string $userIdentifier, ?string $password): void
    {
        $response = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no user identifier, no password' => [
                'userIdentifier' => null,
                'password' => null,
            ],
            'empty user identifier, no password' => [
                'userIdentifier' => '',
                'password' => null,
            ],
            'no user identifier, empty password' => [
                'userIdentifier' => null,
                'password' => '',
            ],
            'empty user identifier, empty password' => [
                'userIdentifier' => '',
                'password' => '',
            ],
            'valid user identifier, no password' => [
                'userIdentifier' => 'user1@example.com',
                'password' => null,
            ],
            'valid user identifier, empty password' => [
                'userIdentifier' => 'user1@example.com',
                'password' => '',
            ],
            'valid user identifier, invalid password' => [
                'userIdentifier' => 'user1@example.com',
                'password' => md5((string) rand()),
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $response = $this->applicationClient->makeCreateUserTokenRequest('user1@example.com', 'password');
        $this->assertRefreshTokenResponse($response);
    }
}
