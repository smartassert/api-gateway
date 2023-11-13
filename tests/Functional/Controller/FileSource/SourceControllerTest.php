<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\FileSourceClientInterface;
use Symfony\Component\Uid\Ulid;

class SourceControllerTest extends AbstractApplicationTestCase
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
        $token = md5((string) rand());
        $label = md5((string) rand());

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('create')
            ->with($token, $label)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeCreateFileSourceRequest($token, $label);

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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('get')
            ->with($token, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($token, 'GET', $sourceId);

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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();
        $label = md5((string) rand());

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('update')
            ->with($token, $sourceId, $label)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($token, 'PUT', $sourceId, $label);

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
        $token = md5((string) rand());
        $sourceId = (string) new Ulid();

        $fileSourceClient = \Mockery::mock(FileSourceClientInterface::class);
        $fileSourceClient
            ->shouldReceive('delete')
            ->with($token, $sourceId)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileSourceClientInterface::class, $fileSourceClient);

        $response = $this->applicationClient->makeFileSourceRequest($token, 'DELETE', $sourceId);

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
