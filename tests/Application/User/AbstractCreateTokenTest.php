<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractCreateTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeCreateUserTokenRequest(
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
        $response = self::$staticApplicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

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
                'userIdentifier' => 'user@example.com',
                'password' => null,
            ],
            'valid user identifier, empty password' => [
                'userIdentifier' => 'user@example.com',
                'password' => '',
            ],
            'valid user identifier, invalid password' => [
                'userIdentifier' => 'user@example.com',
                'password' => md5((string) rand()),
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $response = self::$staticApplicationClient->makeCreateUserTokenRequest(
            'user@example.com',
            'password'
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
