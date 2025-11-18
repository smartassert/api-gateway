<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Exception\Http\ClientException;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class RequestHandlerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testHandleHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $apiToken = md5((string) rand());

        $httpMockHandler->append(
            new Response(
                200,
                ['content-type' => 'application/json'],
                (string) json_encode([
                    'token' => $apiToken,
                ])
            )
        );
        $httpMockHandler->append($httpFixture);

        $apiKey = md5((string) rand());
        $label = md5((string) rand());

        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey, $label);

        self::assertSame($expectedStatusCode, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertEquals($expectedData, $responseData);
    }

    /**
     * @return array<mixed>
     */
    public static function serviceExceptionDataProvider(): array
    {
        return array_merge(
            self::serviceBadResponseContentTypeDataProvider('source', 'application/json'),
            self::serviceHttpFailureDataProvider('source'),
        );
    }

    /**
     * @return array<mixed>
     */
    private static function serviceBadResponseContentTypeDataProvider(
        string $serviceName,
        string $expectedContentType
    ): array {
        return [
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
                        'expected_content_type' => $expectedContentType,
                        'actual_content_type' => 'text/html',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    private static function serviceHttpFailureDataProvider(string $serviceName): array
    {
        $exceptionMessage = md5((string) rand());
        $exceptionCode = rand();

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
        ];
    }
}
