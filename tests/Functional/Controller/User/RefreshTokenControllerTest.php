<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\ServiceBadResponseContentTypeDataProviderTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class RefreshTokenControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use AssertJsonResponseTrait;
    use ServiceBadResponseContentTypeDataProviderTrait;
    use ServiceHttpFailureDataProviderTrait;

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRevokeAllForUserHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $token = md5((string) rand());
        $id = md5((string) rand());

        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

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

    /**
     * @return array<mixed>
     */
    public function serviceExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceBadResponseContentTypeDataProvider('users', 'application/json'),
            $this->serviceHttpFailureDataProvider('users'),
        );
    }
}
