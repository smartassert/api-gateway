<?php

declare(strict_types=1);

namespace App\Tests\Application;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertBadRequestTrait
{
    /**
     * @param array<mixed> $expectedFieldData
     */
    public function assertBadRequest(
        ResponseInterface $response,
        string $expectedErrorType,
        array $expectedFieldData,
    ): void {
        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        Assert::assertArrayHasKey('class', $responseData);
        Assert::assertSame('bad_request', $responseData['class']);

        Assert::assertArrayHasKey('type', $responseData);
        Assert::assertSame($expectedErrorType, $responseData['type']);

        Assert::assertArrayHasKey('field', $responseData);
        Assert::assertSame($expectedFieldData, $responseData['field']);
    }
}
