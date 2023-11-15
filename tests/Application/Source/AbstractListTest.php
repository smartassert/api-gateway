<?php

declare(strict_types=1);

namespace App\Tests\Application\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\FileSource\AssertFileSourceTrait;
use App\Tests\Services\DataRepository;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;

abstract class AbstractListTest extends AbstractApplicationTestCase
{
    use AssertFileSourceTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeListSourcesRequest($token);

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

    public function testListSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source']);

        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $apiToken = $apiTokenProvider->get('user@example.com');

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiToken);
        $this->assertListResponse($listResponse, []);

        $fileSource1Label = md5((string) rand());
        $fileSource1Response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $fileSource1Label);
        $fileSource1Id = $this->extractSourceIdFromResponse($fileSource1Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiToken);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
            ],
        ]);

        $fileSource2Label = md5((string) rand());
        $fileSource2Response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $fileSource2Label);
        $fileSource2Id = $this->extractSourceIdFromResponse($fileSource2Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiToken);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
            ],
        ]);

        $gitSource1Label = md5((string) rand());
        $gitSource1HostUrl = md5((string) rand());
        $gitSource1Path = md5((string) rand());

        $gitSource1Response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $gitSource1Label,
            $gitSource1HostUrl,
            $gitSource1Path,
            null,
        );
        $gitSource1Id = $this->extractSourceIdFromResponse($gitSource1Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiToken);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
            ],
            [
                'id' => $gitSource1Id,
                'label' => $gitSource1Label,
                'type' => 'git',
                'host_url' => $gitSource1HostUrl,
                'path' => $gitSource1Path,
                'has_credentials' => false,
            ],
        ]);

        $gitSource2Label = md5((string) rand());
        $gitSource2HostUrl = md5((string) rand());
        $gitSource2Path = md5((string) rand());

        $gitSource2Response = $this->applicationClient->makeCreateGitSourceRequest(
            $apiKey->key,
            $gitSource2Label,
            $gitSource2HostUrl,
            $gitSource2Path,
            md5((string) rand()),
        );
        $gitSource2Id = $this->extractSourceIdFromResponse($gitSource2Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiToken);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
            ],
            [
                'id' => $gitSource1Id,
                'label' => $gitSource1Label,
                'type' => 'git',
                'host_url' => $gitSource1HostUrl,
                'path' => $gitSource1Path,
                'has_credentials' => false,
            ],
            [
                'id' => $gitSource2Id,
                'label' => $gitSource2Label,
                'type' => 'git',
                'host_url' => $gitSource2HostUrl,
                'path' => $gitSource2Path,
                'has_credentials' => true,
            ],
        ]);
    }

    /**
     * @param array<mixed> $expectedSources
     */
    private function assertListResponse(ResponseInterface $response, array $expectedSources): void
    {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $data = json_decode($response->getBody()->getContents(), true);

        self::assertIsArray($data);
        self::assertArrayHasKey('sources', $data);

        $sourcesData = $data['sources'];
        self::assertIsArray($sourcesData);
        self::assertSame($expectedSources, $sourcesData);
    }

    /**
     * @return non-empty-string
     */
    private function extractSourceIdFromResponse(ResponseInterface $response): string
    {
        $data = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($data));

        if (array_key_exists('file_source', $data)) {
            $sourceData = $data['file_source'];
        }

        if (array_key_exists('git_source', $data)) {
            $sourceData = $data['git_source'];
        }

        $id = $sourceData['id'] ?? '';
        $id = is_string($id) ? $id : '';
        \assert('' !== $id);

        return $id;
    }
}
