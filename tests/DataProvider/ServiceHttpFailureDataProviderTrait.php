<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\Tests\Exception\Http\ClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;

trait ServiceHttpFailureDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function serviceHttpFailureDataProvider(): array
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
        ];
    }
}
