<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\UserController;
use App\Response\ErrorResponse;
use App\Security\AuthenticationToken;
use App\Security\UserCredentials;
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

class UserControllerTest extends TestCase
{
    /**
     * @dataProvider createUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testCreate(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());
        $userCredentials = new UserCredentials($userIdentifier, $password);
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(Client::class);
        $client
            ->shouldReceive('createUser')
            ->with($token, $userIdentifier, $password)
            ->andThrow($exception)
        ;

        $controller = new UserController($client);
        $response = $controller->create($authenticationToken, $userCredentials);

        self::assertSame($expectedResponseStatusCode, $response->getStatusCode());
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame('application/json', $response->headers->get('content-type'));

        $responseData = json_decode((string) $response->getContent(), true);

        self::assertEquals($expectedResponseData, $responseData);
    }

    /**
     * @return array<mixed>
     */
    public function createUsersClientExceptionDataProvider(): array
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
}
