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
        Assert::assertArrayHasKey('file_source', $responseData);

        $objectData = $responseData['file_source'];
        Assert::assertIsArray($objectData);

        $expectedId = is_string($expectedId) ? $expectedId : $objectData['id'];

        Assert::assertSame(
            [
                'id' => $expectedId,
                'label' => $expectedLabel,
                'type' => 'file',
            ],
            $objectData
        );
    }
}
