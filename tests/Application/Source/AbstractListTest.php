<?php

declare(strict_types=1);

namespace App\Tests\Application\Source;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\FileSource\AssertFileSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use App\Tests\Services\DataRepository;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\UserProvider;

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
        $response = $this->applicationClient->makeListSourcesRequest($token);

        self::assertSame(401, $response->getStatusCode());
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

        $userProvider = self::getContainer()->get(UserProvider::class);
        \assert($userProvider instanceof UserProvider);
        $user = $userProvider->get('user@example.com');

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiKey->key);
        $this->assertListResponse($listResponse, []);

        $fileSource1Label = md5((string) rand());
        $fileSource1Response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $fileSource1Label);
        $fileSource1Id = $this->extractSourceIdFromResponse($fileSource1Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiKey->key);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
        ]);

        $fileSource2Label = md5((string) rand());
        $fileSource2Response = $this->applicationClient->makeCreateFileSourceRequest($apiKey->key, $fileSource2Label);
        $fileSource2Id = $this->extractSourceIdFromResponse($fileSource2Response);

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiKey->key);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
        ]);

        $gitSource1Label = md5((string) rand());
        $gitSource1HostUrl = md5((string) rand());
        $gitSource1Path = md5((string) rand());

        $gitSource1Id = $this->createGitSource(
            $apiKey->key,
            $gitSource1Label,
            $gitSource1HostUrl,
            $gitSource1Path,
            null,
        );

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiKey->key);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
            [
                'id' => $gitSource1Id,
                'label' => $gitSource1Label,
                'type' => 'git',
                'host_url' => $gitSource1HostUrl,
                'path' => $gitSource1Path,
                'has_credentials' => false,
                'user_id' => $user->id,
            ],
        ]);

        $gitSource2Label = md5((string) rand());
        $gitSource2HostUrl = md5((string) rand());
        $gitSource2Path = md5((string) rand());

        $gitSource2Id = $this->createGitSource(
            $apiKey->key,
            $gitSource2Label,
            $gitSource2HostUrl,
            $gitSource2Path,
            md5((string) rand()),
        );

        $listResponse = $this->applicationClient->makeListSourcesRequest($apiKey->key);
        $this->assertListResponse($listResponse, [
            [
                'id' => $fileSource1Id,
                'label' => $fileSource1Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
            [
                'id' => $fileSource2Id,
                'label' => $fileSource2Label,
                'type' => 'file',
                'user_id' => $user->id,
            ],
            [
                'id' => $gitSource1Id,
                'label' => $gitSource1Label,
                'type' => 'git',
                'host_url' => $gitSource1HostUrl,
                'path' => $gitSource1Path,
                'has_credentials' => false,
                'user_id' => $user->id,
            ],
            [
                'id' => $gitSource2Id,
                'label' => $gitSource2Label,
                'type' => 'git',
                'host_url' => $gitSource2HostUrl,
                'path' => $gitSource2Path,
                'has_credentials' => true,
                'user_id' => $user->id,
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

        self::assertEquals($expectedSources, $data);
    }

    /**
     * @return non-empty-string
     */
    private function extractSourceIdFromResponse(ResponseInterface $response): string
    {
        $data = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($data));

        $id = $data['id'] ?? '';
        $id = is_string($id) ? $id : '';
        \assert('' !== $id);

        return $id;
    }
}
