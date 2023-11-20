<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\SourcesClient\SuiteClientInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;
use SmartAssert\UsersClient\Model\Token;

class SuiteControllerTest extends AbstractApplicationTestCase
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
        $sourceId = md5((string) rand());
        $label = md5((string) rand());
        $tests = [];

        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('createApiToken')
            ->with($apiKey)
            ->andReturn(new Token($apiToken))
        ;

        $suiteClient = \Mockery::mock(SuiteClientInterface::class);
        $suiteClient
            ->shouldReceive('create')
            ->with($apiToken, $sourceId, $label, $tests)
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);
        self::getContainer()->set(SuiteClientInterface::class, $suiteClient);

        $response = $this->applicationClient->makeCreateSuiteRequest($apiKey, $sourceId, $label, $tests);
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
