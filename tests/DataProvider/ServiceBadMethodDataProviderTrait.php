<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use GuzzleHttp\Psr7\Response;

trait ServiceBadMethodDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function serviceBadMethodProvider(): array
    {
        $serviceName = 'sources';

        return [
            '405, no response content type' => [
                'httpFixture' => new Response(
                    status: 405,
                    reason: 'Method not allowed.'
                ),
                'expectedStatusCode' => 500,
                'expectedData' => [
                    'type' => 'service-communication-failure',
                    'context' => [
                        'service' => $serviceName,
                        'code' => 405,
                        'reason' => 'Method not allowed.',
                        'expected_content_type' => 'application/json',
                        'actual_content_type' => null,
                    ],
                ],
            ],
        ];
    }
}
