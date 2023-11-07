<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\User;

use App\Controller\User\RefreshTokenController;
use App\Response\ErrorResponse;
use App\Security\AuthenticationToken;
use App\Security\RefreshToken;
use App\Security\UserId;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\ClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RefreshTokenControllerTest extends TestCase
{
    /**
     * @dataProvider revokeRefreshTokenUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testRevokeAllForUser(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);
        $id = md5((string) rand());
        $userId = new UserId($id);

        $client = \Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('revokeFrontendRefreshTokensForUser')
            ->with($token, $id)
            ->andThrow($exception)
        ;

        $controller = new RefreshTokenController($client);
        $response = $controller->revokeAllForUser($authenticationToken, $userId);

        self::assertSame($expectedResponseStatusCode, $response->getStatusCode());
        self::assertInstanceOf(ErrorResponse::class, $response);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame('application/json', $response->headers->get('content-type'));

        $responseData = json_decode((string) $response->getContent(), true);

        self::assertEquals($expectedResponseData, $responseData);
    }

    /**
     * @dataProvider revokeRefreshTokenUsersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedResponseData
     */
    public function testRevoke(
        \Exception $exception,
        int $expectedResponseStatusCode,
        array $expectedResponseData,
    ): void {
        $token = md5((string) rand());
        $authenticationToken = new AuthenticationToken($token);
        $refreshTokenValue = md5((string) rand());
        $refreshToken = new RefreshToken($refreshTokenValue);

        $client = \Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('revokeFrontendRefreshToken')
            ->with($token, $refreshTokenValue)
            ->andThrow($exception)
        ;

        $controller = new RefreshTokenController($client);
        $response = $controller->revoke($authenticationToken, $refreshToken);

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
    public function revokeRefreshTokenUsersClientExceptionDataProvider(): array
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
}
