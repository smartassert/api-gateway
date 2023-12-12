<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;

abstract class AbstractVerifyTokenTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertUserResponseTrait;

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testVerifyBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeVerifyUserTokenRequest('token', $method);

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
        $response = $this->applicationClient->makeVerifyUserTokenRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testVerifySuccess(): void
    {
        $frontendTokenProvider = self::getContainer()->get(FrontendTokenProvider::class);
        \assert($frontendTokenProvider instanceof FrontendTokenProvider);
        $frontendToken = $frontendTokenProvider->get('user@example.com');

        $verifyResponse = $this->applicationClient->makeVerifyUserTokenRequest($frontendToken->token);

        $this->assertUserResponse($verifyResponse, 200, 'user@example.com');
    }
}
