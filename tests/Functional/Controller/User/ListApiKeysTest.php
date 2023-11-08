<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class ListApiKeysTest extends AbstractApplicationTestCase
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
    public function testListHandlesException(\Exception $exception, int $expectedStatusCode, array $expectedData): void
    {
        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('listUserApiKeys')
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->staticApplicationClient->makeListUserApiKeysRequest('token');

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @dataProvider usersClientExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testGetDefaultHandlesException(
        \Exception $exception,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $usersClient = \Mockery::mock(UsersClient::class);
        $usersClient
            ->shouldReceive('getUserDefaultApiKey')
            ->andThrow($exception)
        ;

        self::getContainer()->set(UsersClient::class, $usersClient);

        $response = $this->staticApplicationClient->makeGetUserDefaultApiKeyRequest('token');

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function usersClientExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceHttpFailureDataProviderCreator('users'),
            $this->invalidResponseModelDataProviderCreator('users'),
        );
    }
}
