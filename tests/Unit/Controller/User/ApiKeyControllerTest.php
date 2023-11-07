<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\User\ApiKeyController;
use App\Response\ErrorResponse;
use App\Security\AuthenticationToken;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;
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
use SmartAssert\UsersClient\ClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyControllerTest extends TestCase
{
    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testList(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('listUserApiKeys')
            ->with($token)
            ->andThrow($exception)
        ;

        $controller = new ApiKeyController($client);
        $response = $controller->list($authenticationToken);

        $this->assertResponse($response, $expectedResponseStatusCode, $expectedResponseData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testGetDefault(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);

        $client = \Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('getUserDefaultApiKey')
            ->with($token)
            ->andThrow($exception)
        ;

        $controller = new ApiKeyController($client);
        $response = $controller->getDefault($authenticationToken);

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
