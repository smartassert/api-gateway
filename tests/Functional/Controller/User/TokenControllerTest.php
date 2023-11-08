<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class TokenControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use InvalidResponseModelDataProviderCreatorTrait;

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testCreateHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createFrontendToken')
            ->with($userIdentifier, $password)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->staticApplicationClient->makeCreateUserTokenRequest($userIdentifier, $password);

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testVerifyHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('verifyFrontendToken')
            ->with($token)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->staticApplicationClient->makeVerifyUserTokenRequest($token);

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRefreshHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $refreshToken = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('refreshFrontendToken')
            ->with($refreshToken)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->staticApplicationClient->makeRefreshUserTokenRequest($refreshToken);

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function usersClientExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProviderCreator('users'),
            $this->invalidResponseModelDataProviderCreator('users'),
        );
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
