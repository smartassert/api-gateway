<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\FileClientInterface;
use Symfony\Component\Uid\Ulid;

class FileSourceFileControllerTest extends AbstractApplicationTestCase
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
    public function testAddHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        $fileClient = \Mockery::mock(FileClientInterface::class);
        $fileClient
            ->shouldReceive('add')
            ->with($token, $fileSourceId, $filename, $content)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileClientInterface::class, $fileClient);

        $response = $this->applicationClient->makeFileSourceFileRequest(
            $token,
            $fileSourceId,
            $filename,
            'POST',
            $content
        );

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testReadHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';

        $fileClient = \Mockery::mock(FileClientInterface::class);
        $fileClient
            ->shouldReceive('read')
            ->with($token, $fileSourceId, $filename)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileClientInterface::class, $fileClient);

        $response = $this->applicationClient->makeFileSourceFileRequest($token, $fileSourceId, $filename, 'GET');

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testRemoveHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $token = md5((string) rand());
        $fileSourceId = (string) new Ulid();
        $filename = md5((string) rand()) . '.yaml';

        $fileClient = \Mockery::mock(FileClientInterface::class);
        $fileClient
            ->shouldReceive('remove')
            ->with($token, $fileSourceId, $filename)
            ->andThrow($exception)
        ;

        self::getContainer()->set(FileClientInterface::class, $fileClient);

        $response = $this->applicationClient->makeFileSourceFileRequest($token, $fileSourceId, $filename, 'DELETE');

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function usersClientExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProviderCreator('sources'),
        );
    }
}