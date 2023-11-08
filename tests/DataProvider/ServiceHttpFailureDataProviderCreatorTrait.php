<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\Tests\Exception\Http\ClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

trait ServiceHttpFailureDataProviderCreatorTrait
{
    /**
     * @return array<mixed>
     */
    public function serviceHttpFailureDataProviderCreator(string $serviceName): array
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
                        'service' => $serviceName,
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
                        'service' => $serviceName,
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
                        'service' => $serviceName,
                        'status' => 405,
                        'message' => '405: Method not allowed.',
                    ],
                ],
            ],
        ];
    }
}
