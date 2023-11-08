<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertJsonResponseTrait
{
    /**
     * @param array<mixed> $expectedData
     */
    private function assertJsonResponse(ResponseInterface $response, int $expectedCode, array $expectedData): void
    {
        Assert::assertSame($expectedCode, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        Assert::assertEquals($expectedData, $responseData);
    }
}
