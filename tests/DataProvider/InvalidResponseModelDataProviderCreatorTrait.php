<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Response\JsonResponse as ServiceClientJsonResponse;
use SmartAssert\ServiceClient\Response\Response as ServiceClientResponse;

trait InvalidResponseModelDataProviderCreatorTrait
{
    /**
     * @return array<mixed>
     */
    public function invalidResponseModelDataProviderCreator(string $serviceName): array
    {
        $exceptionCode = rand();

        return [
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

                        $body = \Mockery::mock(StreamInterface::class);
                        $body
                            ->shouldReceive('getContents')
                            ->andReturn(json_encode(true))
                        ;

                        $response
                            ->shouldReceive('getBody')
                            ->andReturn($body)
                        ;

                        return $response;
                    })($exceptionCode),
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'invalid-response-data',
                    'context' => [
                        'service' => $serviceName,
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
                        'service' => $serviceName,
                        'content-type' => [
                            'expected' => ServiceClientJsonResponse::class,
                            'actual' => ServiceClientResponse::class,
                        ],
                    ],
                ],
            ],
        ];
    }
}
