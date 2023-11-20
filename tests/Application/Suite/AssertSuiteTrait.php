<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertSuiteTrait
{
    /**
     * @param non-empty-string[] $expectedTests
     */
    public function assertRetrievedSuite(
        ResponseInterface $response,
        string $expectedSourceId,
        string $expectedLabel,
        array $expectedTests,
        ?string $expectedId = null,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);
        Assert::assertArrayHasKey('suite', $responseData);

        $objectData = $responseData['suite'];
        Assert::assertIsArray($objectData);

        $expectedId = is_string($expectedId) ? $expectedId : $objectData['id'];

        Assert::assertSame(
            [
                'id' => $expectedId,
                'source_id' => $expectedSourceId,
                'label' => $expectedLabel,
                'tests' => $expectedTests,
            ],
            $objectData
        );
    }
}
