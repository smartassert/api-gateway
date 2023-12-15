<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Application\User\AssertRefreshTokenResponseTrait;

abstract class AbstractUndefinedServiceTest extends AbstractApplicationTestCase
{
    use AssertRefreshTokenResponseTrait;

    /**
     * @dataProvider makeRequestForUndefinedServiceDataProvider
     *
     * @param array{service: string, action: string} $expectedResponseDataContext
     */
    public function testMakeRequestForUndefinedService(string $url, array $expectedResponseDataContext): void
    {
        $response = $this->applicationClient->makeUndefinedServiceRequest($url);

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertSame(
            [
                'type' => 'undefined-service',
                'context' => $expectedResponseDataContext,
            ],
            $responseData
        );
    }

    /**
     * @return array<mixed>
     */
    public function makeRequestForUndefinedServiceDataProvider(): array
    {
        $serviceName = str_replace([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], '', md5((string) rand()));
        $action = md5((string) rand());

        return [
            'undefined service, basic action' => [
                'url' => '/' . $serviceName . '/' . $action,
                'expectedResponseDataContext' => [
                    'service' => $serviceName,
                    'action' => $action,
                ],
            ],
            'undefined service, complex action with query string' => [
                'url' => '/' . $serviceName . '/' . $action . '/' . $action . '?' . $action,
                'expectedResponseDataContext' => [
                    'service' => $serviceName,
                    'action' => $action . '/' . $action,
                ],
            ],
        ];
    }
}
