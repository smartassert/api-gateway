<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use GuzzleHttp\Psr7\Response;

trait ServiceBadResponseContentTypeDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function serviceBadResponseContentTypeDataProvider(): array
    {
        $serviceName = 'sources';

        return [
            '200, text/html content type' => [
                'httpFixture' => new Response(
                    status: 200,
                    headers: ['content-type' => 'text/html'],
                    body: '<html />',
                    reason: 'Ok.'
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => $serviceName,
                        'code' => 200,
                        'reason' => 'Ok.',
                        'expected_content_type' => 'application/json',
                        'actual_content_type' => 'text/html',
                    ],
                ],
            ],
        ];
    }
}
