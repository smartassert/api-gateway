<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Exception\Http\ClientException;
use App\Tests\Functional\GetClientAdapterTrait;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Response\JsonResponse as ServiceClientJsonResponse;
use SmartAssert\ServiceClient\Response\Response as ServiceClientResponse;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class TokenControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;

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
        $exceptionMessage = md5((string) rand());
        $exceptionCode = rand();

        return [
            ClientExceptionInterface::class => [
                'exception' => new ClientException(
                    $exceptionMessage,
                    $exceptionCode
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => 'users',
                        'error' => [
                            'code' => $exceptionCode,
                            'message' => $exceptionMessage,
                        ],
                    ],
                ],
            ],
            CurlExceptionInterface::class => [
                'exception' => new CurlException(
                    \Mockery::mock(RequestInterface::class),
                    $exceptionCode,
                    $exceptionMessage,
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => 'users',
                        'error' => [
                            'code' => $exceptionCode,
                            'message' => $exceptionMessage,
                        ],
                    ],
                ],
            ],
            InvalidResponseDataException::class => [
                'exception' => new InvalidResponseDataException(
                    'array',
                    'bool',
                    (function (int $exceptionCode) {
                        $response = \Mockery::mock(ResponseInterface::class);
                        $response
                            ->shouldReceive('getStatusCode')
                            ->andReturn($exceptionCode)
                        ;

                        $response
                            ->shouldReceive('getBody')
                            ->andReturn(json_encode(true))
                        ;

                        return $response;
                    })($exceptionCode),
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'invalid-response-data',
                    'context' => [
                        'service' => 'users',
                        'data' => 'true',
                        'data-type' => [
                            'expected' => 'array',
                            'actual' => 'bool',
                        ],
                    ],
                ],
            ],
            InvalidResponseTypeException::class => [
                'exception' => new InvalidResponseTypeException(
                    (function (int $exceptionCode) {
                        $response = \Mockery::mock(ResponseInterface::class);
                        $response
                            ->shouldReceive('getStatusCode')
                            ->andReturn($exceptionCode)
                        ;

                        return $response;
                    })($exceptionCode),
                    ServiceClientJsonResponse::class,
                    ServiceClientResponse::class,
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'invalid-response-type',
                    'context' => [
                        'service' => 'users',
                        'content-type' => [
                            'expected' => ServiceClientJsonResponse::class,
                            'actual' => ServiceClientResponse::class,
                        ],
                    ],
                ],
            ],
            NonSuccessResponseException::class . ' 404' => [
                'exception' => new NonSuccessResponseException(
                    (function () {
                        $response = \Mockery::mock(ResponseInterface::class);
                        $response
                            ->shouldReceive('getStatusCode')
                            ->andReturn(404)
                        ;

                        $response
                            ->shouldReceive('getReasonPhrase')
                            ->andReturn('Not found.')
                        ;

                        return $response;
                    })(),
                ),
                'expectedStatusCode' => 404,
                'expectedData' => [
                    'type' => 'not-found',
                ],
            ],
            NonSuccessResponseException::class . ' 405' => [
                'exception' => new NonSuccessResponseException(
                    (function () {
                        $response = \Mockery::mock(ResponseInterface::class);
                        $response
                            ->shouldReceive('getStatusCode')
                            ->andReturn(405)
                        ;

                        $response
                            ->shouldReceive('getReasonPhrase')
                            ->andReturn('Method not allowed.')
                        ;

                        return $response;
                    })(),
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'non-successful-service-response',
                    'context' => [
                        'service' => 'users',
                        'status' => 405,
                        'message' => '405: Method not allowed.',
                    ],
                ],
            ],
        ];
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
