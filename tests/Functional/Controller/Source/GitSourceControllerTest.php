<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\GitSourceClientInterface;
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
        $token = md5((string) rand());
        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());
        $credentials = md5((string) rand());

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('create')
            ->with($token, $label, $hostUrl, $path, $credentials)
            ->andThrow($exception)
        ;

        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeCreateGitSourceRequest($token, $label, $hostUrl, $path, $credentials);

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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('get')
            ->with($token, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest($token, 'GET', $sourceId);

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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();
        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());
        $credentials = md5((string) rand());

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('update')
            ->with($token, $sourceId, $label, $hostUrl, $path, $credentials)
            ->andThrow($exception)
        ;

        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest(
            $token,
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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();

        $gitSourceClient = \Mockery::mock(GitSourceClientInterface::class);
        $gitSourceClient
            ->shouldReceive('delete')
            ->with($token, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(GitSourceClientInterface::class, $gitSourceClient);

        $response = $this->applicationClient->makeGitSourceRequest($token, 'DELETE', $sourceId);

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
