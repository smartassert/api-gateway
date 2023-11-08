<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractCreateUserTest extends AbstractApplicationTestCase
{
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

    public function testCreateUserUserAlreadyExists(): void
    {
        $userIdentifier = 'user@example.com';
        $password = 'password';

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        self::assertSame(200, $createTokenResponse->getStatusCode());

        $response = $this->applicationClient->makeCreateUserRequest('primary_admin_token', $userIdentifier, $password);

        self::assertSame(409, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertSame(['type' => 'user-already-exists'], $responseData);
    }

    public function testCreateUserSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        self::assertSame(401, $createTokenResponse->getStatusCode());

        $response = $this->applicationClient->makeCreateUserRequest('primary_admin_token', $userIdentifier, $password);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);
        self::assertArrayHasKey('user', $responseData);

        $userData = $responseData['user'];
        self::assertIsArray($userData);
        self::assertArrayHasKey('id', $userData);
        self::assertArrayHasKey('user-identifier', $userData);

        $createTokenResponse = $this->applicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        self::assertSame(200, $createTokenResponse->getStatusCode());
    }
}
