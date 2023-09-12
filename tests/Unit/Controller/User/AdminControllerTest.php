<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\AdminController;
use App\Response\ErrorResponse;
use App\Security\AuthenticationToken;
use App\Security\UserCredentials;
use App\Security\UserId;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends TestCase
{
    /**
     * @dataProvider createUserUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testCreateUser(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $email = md5((string) rand());
        $password = md5((string) rand());
        $userCredentials = new UserCredentials($email, $password);
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('createUser')
            ->with($token, $email, $password)
            ->andThrow($exception)
        ;

        $controller = new AdminController($client);
        $response = $controller->createUser($authenticationToken, $userCredentials);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
    }

    /**
     * @return array<mixed>
     */
    public function createUserUsersClientExceptionDataProvider(): array
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
     * @dataProvider revokeFrontendRefreshTokenUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testRevokeFrontendRefreshToken(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);
        $id = md5((string) rand());
        $userId = new UserId($id);

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('revokeFrontendRefreshToken')
            ->with($token, $id)
            ->andThrow($exception)
        ;

        $controller = new AdminController($client);
        $response = $controller->revokeFrontendRefreshToken($authenticationToken, $userId);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
    }

    /**
     * @return array<mixed>
     */
    public function revokeFrontendRefreshTokenUsersClientExceptionDataProvider(): array
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
