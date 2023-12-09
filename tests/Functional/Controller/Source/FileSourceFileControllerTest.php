<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceBadResponseContentTypeDataProviderTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class FileSourceFileControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use InvalidResponseModelDataProviderCreatorTrait;
    use AssertJsonResponseTrait;
    use ServiceBadResponseContentTypeDataProviderTrait;
    use ServiceHttpFailureDataProviderTrait;

    /**
     * @dataProvider addDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testAddHandlesException(
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
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeCreateFileSourceFileRequest(
            $apiKey,
            $fileSourceId,
            $filename,
            $content
        );

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function addDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProvider('sources'),
        );
    }

    /**
     * @dataProvider readDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testReadHandlesException(
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
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeReadFileSourceFileRequest($apiKey, $fileSourceId, $filename);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function readDataProvider(): array
    {
        return array_merge(
            $this->serviceBadResponseContentTypeDataProvider('sources', 'text/x-yaml'),
            $this->serviceHttpFailureDataProvider('sources'),
        );
    }

    /**
     * @dataProvider addDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRemoveHandlesException(
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
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeDeleteFileSourceFileRequest($apiKey, $fileSourceId, $filename);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }
}
