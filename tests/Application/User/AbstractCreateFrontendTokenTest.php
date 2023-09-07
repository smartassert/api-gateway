<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;

abstract class AbstractCreateFrontendTokenTest extends AbstractApplicationTestCase
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = self::$staticApplicationClient->makeCreateUserFrontendTokenRequest(
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
    public function testCreateUnauthorizedUser(?string $email, ?string $password): void
    {
        $response = self::$staticApplicationClient->makeCreateUserFrontendTokenRequest($email, $password);

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no email, no password' => [
                'email' => null,
                'password' => null,
            ],
            'empty email, no password' => [
                'email' => '',
                'password' => null,
            ],
            'no email, empty password' => [
                'email' => null,
                'password' => '',
            ],
            'empty email, empty password' => [
                'email' => '',
                'password' => '',
            ],
            'valid email, no password' => [
                'email' => 'user@example.com',
                'password' => null,
            ],
            'valid email, empty password' => [
                'email' => 'user@example.com',
                'password' => '',
            ],
            'valid email, invalid password' => [
                'email' => 'user@example.com',
                'password' => md5((string) rand()),
            ],
        ];
    }

    public function testCreateSuccess(): void
    {
        $response = self::$staticApplicationClient->makeCreateUserFrontendTokenRequest(
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
