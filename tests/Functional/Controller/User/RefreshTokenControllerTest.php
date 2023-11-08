<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class RefreshTokenControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use AssertJsonResponseTrait;

    /**
     * @dataProvider revokeRefreshTokenUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRevokeAllForUserHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());
        $id = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('revokeFrontendRefreshTokensForUser')
            ->with($token, $id)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->applicationClient->makeRevokeAllRefreshTokensForUserRequest($token, $id);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider revokeRefreshTokenUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRevokeHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('revokeFrontendRefreshToken')
            ->with($token, $refreshToken)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->applicationClient->makeRevokeRefreshTokenRequest($token, $refreshToken);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function revokeRefreshTokenUsersClientExceptionDataProvider(): array
    {
        return $this->serviceHttpFailureDataProviderCreator('users');
    }
}
