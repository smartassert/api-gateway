<?php

declare(strict_types=1);

namespace App\Tests\Application;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertBadRequestTrait
{
    /**
     * @param array<mixed> $expectedParameterData
     */
    public function assertBadRequest(
        ResponseInterface $response,
        string $expectedErrorType,
        array $expectedParameterData,
    ): void {
        $responseData = $this->assertResponse($response);

        self::assertEquals(
            [
                'class' => 'bad_request',
                'type' => $expectedErrorType,
                'parameter' => $expectedParameterData,
            ],
            $responseData
        );
    }

    public function assertDuplicateObjectResponse(
        ResponseInterface $response,
        string $expectedParameter,
        string $expectedValue
    ): void {
        $responseData = $this->assertResponse($response);

        self::assertEquals(
            [
                'class' => 'duplicate',
                'parameter' => [
                    'name' => $expectedParameter,
                    'value' => $expectedValue,
                ],
            ],
            $responseData
        );
    }

    /**
     * @return array<mixed>
     */
    private function assertResponse(ResponseInterface $response): array
    {
        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);

        return $responseData;
    }
}
