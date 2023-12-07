<?php

declare(strict_types=1);

namespace App\Tests\Application;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertBadRequestTrait
{
    public function assertBadRequest(
        ResponseInterface $response,
        string $expectedService,
        string $expectedInvalidField
    ): void {
        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        Assert::assertArrayHasKey('type', $responseData);
        Assert::assertSame('bad-request', $responseData['type']);

        Assert::assertArrayHasKey('context', $responseData);
        $contextData = $responseData['context'];
        Assert::assertIsArray($contextData);

        Assert::assertSame($expectedService, $contextData['service']);
        Assert::assertArrayHasKey('invalid-field', $contextData);

        $invalidFieldData = $contextData['invalid-field'];
        Assert::assertIsArray($invalidFieldData);
        Assert::assertArrayHasKey('name', $invalidFieldData);
        Assert::assertSame($expectedInvalidField, $invalidFieldData['name']);

        Assert::assertArrayHasKey('value', $invalidFieldData);
        Assert::assertSame('', $invalidFieldData['value']);

        Assert::assertArrayHasKey('message', $invalidFieldData);
        Assert::assertNotSame('', $invalidFieldData['message']);
    }

    /**
     * @param array<mixed> $expectedFieldData
     */
    public function assertBadRequestFoo(
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
