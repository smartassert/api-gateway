<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\Tests\Exception\Http\ClientException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;

trait ServiceExceptionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function serviceExceptionDataProvider(): array
    {
        $exceptionMessage = md5((string) rand());
        $exceptionCode = rand();
        $serviceName = 'sources';

        return [
            ClientExceptionInterface::class => [
                'httpFixture' => new ClientException(
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
                'httpFixture' => new CurlException(
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
            '405, no response content type' => [
                'httpFixture' => new Response(
                    status: 405,
                    reason: 'Method not allowed.'
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => $serviceName,
                        'code' => 405,
                        'reason' => 'Method not allowed.',
                        'expected_content_type' => 'application/json',
                        'actual_content_type' => null,
                    ],
                ],
            ],
            '200, text/html content type' => [
                'httpFixture' => new Response(
                    status: 200,
                    headers: ['content-type' => 'text/html'],
                    body: '<html />',
                    reason: 'Ok.'
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => $serviceName,
                        'code' => 200,
                        'reason' => 'Ok.',
                        'expected_content_type' => 'application/json',
                        'actual_content_type' => 'text/html',
                    ],
                ],
            ],
        ];
    }
}
