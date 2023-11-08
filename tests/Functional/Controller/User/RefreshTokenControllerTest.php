<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class RefreshTokenControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;

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

        $response = $this->staticApplicationClient->makeRevokeAllRefreshTokensForUserRequest($token, $id);

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
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

        $response = $this->staticApplicationClient->makeRevokeRefreshTokenRequest($token, $refreshToken);

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function revokeRefreshTokenUsersClientExceptionDataProvider(): array
    {
        return $this->serviceHttpFailureDataProviderCreator('users');
    }

    /**
     * @param array<mixed> $expectedData
     */
    private function assertResponse(ResponseInterface $response, int $expectedCode, array $expectedData): void
    {
        self::assertSame($expectedCode, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertEquals($expectedData, $responseData);
    }
}
