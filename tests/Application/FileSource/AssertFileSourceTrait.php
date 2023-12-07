<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertFileSourceTrait
{
    public function assertRetrievedFileSource(
        ResponseInterface $response,
        string $expectedLabel,
        ?string $expectedUserId = null,
        ?string $expectedId = null,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody, true);
        Assert::assertIsArray($responseData);

        $expectedId = is_string($expectedId) ? $expectedId : $responseData['id'];

        $expectedResponseData = [
            'id' => $expectedId,
            'label' => $expectedLabel,
            'type' => 'file',
        ];

        if (is_string($expectedUserId)) {
            $expectedResponseData['user_id'] = $expectedUserId;
        }

        Assert::assertEquals($expectedResponseData, $responseData);
    }

    public function assertDeletedFileSource(
        ResponseInterface $response,
        string $expectedLabel,
        string $expectedUserId,
        string $expectedId,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        $deletedAt = $responseData['deleted_at'] ?? null;
        Assert::assertIsInt($deletedAt);

        $expectedResponseData = [
            'id' => $expectedId,
            'label' => $expectedLabel,
            'type' => 'file',
            'deleted_at' => $deletedAt,
            'user_id' => $expectedUserId,
        ];

        Assert::assertEquals($expectedResponseData, $responseData);
    }
}
