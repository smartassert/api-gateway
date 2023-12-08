<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Exception\Http\ClientException;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\CurlException;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class FileSourceControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use AssertJsonResponseTrait;

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testCreateHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $label = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey, $label);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testUpdateHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();
        $label = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeUpdateFileSourceRequest($apiKey, $sourceId, $label);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testDeleteHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeDeleteFileSourceRequest($apiKey, $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testListHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeFileSourceFilesRequest($apiKey, $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

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
