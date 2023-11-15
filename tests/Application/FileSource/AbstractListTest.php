<?php

declare(strict_types=1);

namespace App\Tests\Application\FileSource;

use App\Tests\Application\AbstractApplicationTestCase;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractListTest extends AbstractApplicationTestCase
{
    use AssertFileSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeFileSourceFilesRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token' => [
                'token' => null,
            ],
            'empty token' => [
                'token' => '',
            ],
            'non-empty invalid token' => [
                'token' => md5((string) rand()),
            ],
        ];
    }

    public function testListNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeFileSourceFilesRequest($apiKey->key, (string) new Ulid());

        self::assertSame(404, $response->getStatusCode());
    }

    public function testListSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());

        $createResponse = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $label);
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $createdSourceData = $createResponseData['file_source'];
        \assert(is_array($createdSourceData));

        $id = $createdSourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey->key, $id);
        $this->assertListResponse($listResponse, []);

        $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $id,
            'fileZ.yaml',
            'POST',
            md5((string) rand())
        );

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey->key, $id);
        $this->assertListResponse($listResponse, ['fileZ.yaml']);

        $this->applicationClient->makeFileSourceFileRequest(
            $apiKey->key,
            $id,
            'fileA.yaml',
            'POST',
            md5((string) rand())
        );

        $listResponse = $this->applicationClient->makeFileSourceFilesRequest($apiKey->key, $id);
        $this->assertListResponse($listResponse, ['fileA.yaml', 'fileZ.yaml']);
    }

    /**
     * @param string[] $expectedFiles
     */
    private function assertListResponse(ResponseInterface $response, array $expectedFiles): void
    {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $data = json_decode($response->getBody()->getContents(), true);

        self::assertIsArray($data);
        self::assertArrayHasKey('files', $data);

        $retrievedFiles = $data['files'];
        self::assertIsArray($retrievedFiles);
        self::assertSame($expectedFiles, $retrievedFiles);
    }
}
