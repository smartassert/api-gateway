<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\GitSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\GitSourceClientInterface;

class CreationControllerTest extends AbstractApplicationTestCase
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
