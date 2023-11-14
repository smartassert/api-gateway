<?php

declare(strict_types=1);

namespace App\Tests\Application\GitSource;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

trait AssertGitSourceTrait
{
    public function assertRetrievedGitSource(
        ResponseInterface $response,
        string $label,
        ?string $expectedId,
        string $hostUrl,
        string $path,
        bool $expectedHasCredentials,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);
        Assert::assertArrayHasKey('git_source', $responseData);

        $objectData = $responseData['git_source'];
        Assert::assertIsArray($objectData);

        $expectedId = is_string($expectedId) ? $expectedId : $objectData['id'];

        Assert::assertSame(
            [
                'id' => $expectedId,
                'label' => $label,
                'type' => 'git',
                'host_url' => $hostUrl,
                'path' => $path,
                'has_credentials' => $expectedHasCredentials,
            ],
            $objectData
        );
    }

    public function assertDeletedGitSource(
        ResponseInterface $response,
        string $expectedLabel,
        string $expectedId,
        string $expectedHostUrl,
        string $expectedPath,
        bool $expectedHasCredentials,
    ): void {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        Assert::assertIsArray($responseData);
        Assert::assertArrayHasKey('git_source', $responseData);

        $objectData = $responseData['git_source'];
        Assert::assertIsArray($objectData);

        $deletedAt = $objectData['deleted_at'] ?? null;
        Assert::assertIsInt($deletedAt);

        Assert::assertSame(
            [
                'id' => $expectedId,
                'label' => $expectedLabel,
                'type' => 'git',
                'deleted_at' => $deletedAt,
                'host_url' => $expectedHostUrl,
                'path' => $expectedPath,
                'has_credentials' => $expectedHasCredentials,
            ],
            $objectData
        );
    }
}
