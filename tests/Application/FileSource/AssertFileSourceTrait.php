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
        ?string $expectedId = null,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        $expectedId = is_string($expectedId) ? $expectedId : $responseData['id'];

        Assert::assertSame(
            [
                'id' => $expectedId,
                'label' => $expectedLabel,
                'type' => 'file',
            ],
            $responseData
        );
    }

    public function assertDeletedFileSource(
        ResponseInterface $response,
        string $expectedLabel,
        string $expectedId,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        $deletedAt = $responseData['deleted_at'] ?? null;
        Assert::assertIsInt($deletedAt);

        Assert::assertSame(
            [
                'id' => $expectedId,
                'label' => $expectedLabel,
                'type' => 'file',
                'deleted_at' => $deletedAt,
            ],
            $responseData
        );
    }
}
