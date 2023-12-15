<?php

declare(strict_types=1);

namespace App\Tests\Application\User;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertRefreshTokenResponseTrait
{
    private function assertRefreshTokenResponse(ResponseInterface $response): void
    {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);
        Assert::assertArrayHasKey('token', $responseData);
        Assert::assertArrayHasKey('refresh_token', $responseData);
    }
}
