<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class FileSourceControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use InvalidResponseModelDataProviderCreatorTrait;
    use AssertJsonResponseTrait;

    /**
     * @dataProvider sourcesClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testCreateHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $label = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('create')
            ->with($apiToken, $label)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeCreateFileSourceRequest($apiKey, $label);
        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider sourcesClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testGetHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('get')
            ->with($apiToken, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($apiKey, 'GET', $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider sourcesClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testUpdateHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
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

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('update')
            ->with($apiToken, $sourceId, $label)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($apiKey, 'PUT', $sourceId, $label);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider sourcesClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testDeleteHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('delete')
            ->with($apiToken, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($apiKey, 'DELETE', $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider sourcesClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testListHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $apiKey = md5((string) rand());
        $apiToken = md5((string) rand());
        $sourceId = (string) new Ulid();

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('list')
            ->with($apiToken, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceFilesRequest($apiKey, $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function sourcesClientExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProviderCreator('sources'),
            $this->invalidResponseModelDataProviderCreator('sources'),
        );
    }
}
