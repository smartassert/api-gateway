<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\GitSourceClientInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class GitSourceControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use InvalidResponseModelDataProviderCreatorTrait;
    use AssertJsonResponseTrait;

    /**
     * @dataProvider usersClientExceptionDataProvider
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
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());
        $credentials = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('create')
            ->with($apiToken, $label, $hostUrl, $path, $credentials)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey,
            $label,
            $hostUrl,
            $path,
            $credentials
        );
        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
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

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('get')
            ->with($apiToken, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest($apiKey, 'GET', $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
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
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());
        $credentials = md5((string) rand());

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('update')
            ->with($apiToken, $sourceId, $label, $hostUrl, $path, $credentials)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest(
            $apiKey,
            'PUT',
            $sourceId,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
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

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('delete')
            ->with($apiToken, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest($apiKey, 'DELETE', $sourceId);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function usersClientExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProviderCreator('sources'),
            $this->invalidResponseModelDataProviderCreator('sources'),
        );
    }
}
