<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\UserFrontendTokenController;
use App\Response\ErrorResponse;
use App\Security\AuthenticationToken;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Response\JsonResponse as ServiceClientJsonResponse;
use SmartAssert\ServiceClient\Response\Response as ServiceClientResponse;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Model\RefreshableToken as UsersClientRefreshableToken;
use SmartAssert\UsersClient\Model\Token as UsersClientToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserFrontendTokenControllerTest extends TestCase
{
    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testCreate(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $email = md5((string) rand());
        $password = md5((string) rand());

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('createFrontendToken')
            ->with($email, $password)
            ->andThrow($exception)
        ;

        $controller = new UserFrontendTokenController($client);
        $request = new Request([], ['email' => $email, 'password' => $password]);
        $response = $controller->create($request);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testVerify(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('verifyFrontendToken')
            ->withArgs(function (UsersClientToken $usersClientToken) use ($token) {
                self::assertSame($token, $usersClientToken->token);

                return true;
            })
            ->andThrow($exception)
        ;

        $controller = new UserFrontendTokenController($client);
        $response = $controller->verify($authenticationToken);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testRefresh(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('refreshFrontendToken')
            ->withArgs(function (
                UsersClientRefreshableToken $usersClientRefreshableToken
            ) use (
                $token,
                $refreshToken
            ) {
                self::assertSame($token, $usersClientRefreshableToken->token);
                self::assertSame($refreshToken, $usersClientRefreshableToken->refreshToken);

                return true;
            })
            ->andThrow($exception)
        ;

        $request = new Request([], ['refresh_token' => $refreshToken]);

        $controller = new UserFrontendTokenController($client);
        $response = $controller->refresh($authenticationToken, $request);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
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
                'exception' => new TransferException(
                    $exceptionMessage,
                    $exceptionCode
                ),
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
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
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
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
            InvalidModelDataException::class => [
                'exception' => new InvalidModelDataException(
                    (function (int $exceptionCode) {
                        $response = \Mockery::mock(ResponseInterface::class);
                        $response
                            ->shouldReceive('getStatusCode')
                            ->andReturn($exceptionCode)
                        ;

                        $response
                            ->shouldReceive('getBody')
                            ->andReturn(json_encode(['token' => 123]))
                        ;

                        return $response;
                    })($exceptionCode),
                    UsersClientRefreshableToken::class,
                    [],
                ),
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
                    'type' => 'invalid-model-data',
                    'context' => [
                        'service' => 'users',
                        'data' => '{"token":123}',
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
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
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
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
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
                'expectedResponseStatusCode' => 404,
                'expectedResponseData' => [
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
                'expectedResponseStatusCode' => 500,
                'expectedResponseData' => [
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
    private function assertResponse(Response $response, int $expectedCode, array $expectedData): void
    {
        self::assertSame($expectedCode, $response->getStatusCode());
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame('application/json', $response->headers->get('content-type'));

        $responseData = json_decode((string) $response->getContent(), true);

        self::assertEquals($expectedData, $responseData);
    }
}
