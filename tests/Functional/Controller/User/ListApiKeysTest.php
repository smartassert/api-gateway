<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\InvalidResponseModelDataProviderCreatorTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderCreatorTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\ClientInterface as UsersClient;

class ListApiKeysTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceHttpFailureDataProviderCreatorTrait;
    use InvalidResponseModelDataProviderCreatorTrait;

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

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
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

        $this->assertResponse($response, $expectedStatusCode, $expectedData);
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

    /**
     * @param array<mixed> $expectedData
     */
    private function assertResponse(ResponseInterface $response, int $expectedCode, array $expectedData): void
    {
        self::assertSame($expectedCode, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertEquals($expectedData, $responseData);
    }
}
