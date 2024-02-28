<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractListTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertFileSourceTrait;
    use CreateSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeFileSourceFilesRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    public function testListNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFilesRequest($apiKey['key'], (string) new Ulid());

        echo $response->getBody()->getContents();

        self::assertSame(403, $response->getStatusCode());
    }

    public function testListSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $id = $this->createFileSource($apiKey['key'], $label);

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey['key'], $id);
        $this->assertListResponse($listResponse, []);

        $this->applicationClient->makeCreateFileSourceFileRequest(
            $apiKey['key'],
            $id,
            'fileZ.yaml',
            md5((string) rand())
        );

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey['key'], $id);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'path' => 'fileZ.yaml',
                    'size' => 32,
                ],
            ]
        );

        $this->applicationClient->makeCreateFileSourceFileRequest(
            $apiKey['key'],
            $id,
            'fileA.yaml',
            md5((string) rand())
        );

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey['key'], $id);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'path' => 'fileA.yaml',
                    'size' => 32,
                ],
                [
                    'path' => 'fileZ.yaml',
                    'size' => 32,
                ],
            ]
        );
    }

    /**
     * @param array<mixed> $expectedFiles
     */
    private function assertListResponse(ResponseInterface $response, array $expectedFiles): void
    {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $data = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($data);

        self::assertSame($expectedFiles, $data);
    }
}
