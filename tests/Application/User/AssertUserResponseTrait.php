<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertUserResponseTrait
{
    private function assertUserResponse(
        ResponseInterface $response,
        int $expectedStatusCode,
        string $expectedUserIdentifier
    ): void {
        Assert::assertSame($expectedStatusCode, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        Assert::assertArrayHasKey('id', $responseData);
        Assert::assertSame($expectedUserIdentifier, $responseData['user-identifier']);
    }
}
