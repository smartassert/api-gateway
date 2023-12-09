<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\ServiceBadResponseContentTypeDataProviderTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class SourceControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use AssertJsonResponseTrait;
    use ServiceBadResponseContentTypeDataProviderTrait;
    use ServiceHttpFailureDataProviderTrait;

    /**
     * @dataProvider serviceBadResponseContentTypeDataProvider
     * @dataProvider serviceHttpFailureDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testGetHandlesException(
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

        $response = $this->applicationClient->makeSourceActRequest('GET', $apiKey, $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider serviceBadResponseContentTypeDataProvider
     * @dataProvider serviceHttpFailureDataProvider
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

        $response = $this->applicationClient->makeSourceActRequest('DELETE', $apiKey, $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }
}
