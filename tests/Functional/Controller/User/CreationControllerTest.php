<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\DataProvider\ServiceBadResponseContentTypeDataProviderTrait;
use App\Tests\DataProvider\ServiceHttpFailureDataProviderTrait;
use App\Tests\Functional\Controller\AssertJsonResponseTrait;
use App\Tests\Functional\GetClientAdapterTrait;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class CreationControllerTest extends AbstractApplicationTestCase
{
    use GetClientAdapterTrait;
    use ServiceBadResponseContentTypeDataProviderTrait;
    use ServiceHttpFailureDataProviderTrait;
    use AssertJsonResponseTrait;

    /**
     * @dataProvider serviceExceptionDataProvider
     *
     * @param array<mixed> $expectedData
     */
    public function testCreateHandlesException(
        \Exception|ResponseInterface $httpFixture,
        int $expectedStatusCode,
        array $expectedData
    ): void {
        $mockingHttpClient = self::getContainer()->get('app.test.mocking_http_client');
        \assert($mockingHttpClient instanceof HttpClientInterface);

        $httpMockHandler = self::getContainer()->get(MockHandler::class);
        \assert($httpMockHandler instanceof MockHandler);

        $httpMockHandler->append($httpFixture);

        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());
        $token = md5((string) rand());

        self::getContainer()->set(HttpClientInterface::class, $mockingHttpClient);

        $response = $this->applicationClient->makeCreateUserRequest($token, $userIdentifier, $password);

        $this->assertJsonResponse($response, $expectedStatusCode, $expectedData);
    }

    /**
     * @return array<mixed>
     */
    public function serviceExceptionDataProvider(): array
    {
        return array_merge(
            $this->serviceBadResponseContentTypeDataProvider('users', 'application/json'),
            $this->serviceHttpFailureDataProvider('users'),
        );
    }
}
