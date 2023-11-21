<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\CreateSuiteTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use App\Tests\Services\DataRepository;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;

abstract class AbstractListTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;
    use CreateSourceTrait;
    use CreateSuiteTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeListSuitesRequest($token);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testListSuccess(): void
    {
        $sourcesDataRepository = new DataRepository(
            'pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!'
        );
        $sourcesDataRepository->removeAllFor(['file_source', 'git_source', 'source', 'suite']);

        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $listResponse = $this->applicationClient->makeListSuitesRequest($apiKey->key);
        $this->assertListResponse($listResponse, []);

        $source1Id = $this->createFileSource($apiKey->key, md5((string) rand()));
        $suite1Id = $this->createSuite($apiKey->key, $source1Id, 'suite 1 label', []);

        $listResponse = $this->applicationClient->makeListSuitesRequest($apiKey->key);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'id' => $suite1Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 1 label',
                    'tests' => [],
                ],
            ]
        );

        $suite2Id = $this->createSuite($apiKey->key, $source1Id, 'suite 2 label', ['Z.yaml', 'A.yml']);

        $listResponse = $this->applicationClient->makeListSuitesRequest($apiKey->key);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'id' => $suite1Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 1 label',
                    'tests' => [],
                ],
                [
                    'id' => $suite2Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 2 label',
                    'tests' => [
                        'Z.yaml',
                        'A.yml',
                    ],
                ],
            ]
        );

        $source2Id = $this->createFileSource($apiKey->key, md5((string) rand()));
        $suite3Id = $this->createSuite($apiKey->key, $source2Id, 'suite 3 label', []);

        $listResponse = $this->applicationClient->makeListSuitesRequest($apiKey->key);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'id' => $suite1Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 1 label',
                    'tests' => [],
                ],
                [
                    'id' => $suite2Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 2 label',
                    'tests' => [
                        'Z.yaml',
                        'A.yml',
                    ],
                ],
                [
                    'id' => $suite3Id,
                    'source_id' => $source2Id,
                    'label' => 'suite 3 label',
                    'tests' => [],
                ],
            ]
        );

        $this->applicationClient->makeDeleteSuiteRequest($apiKey->key, $suite2Id);

        $listResponse = $this->applicationClient->makeListSuitesRequest($apiKey->key);
        $this->assertListResponse(
            $listResponse,
            [
                [
                    'id' => $suite1Id,
                    'source_id' => $source1Id,
                    'label' => 'suite 1 label',
                    'tests' => [],
                ],
                [
                    'id' => $suite3Id,
                    'source_id' => $source2Id,
                    'label' => 'suite 3 label',
                    'tests' => [],
                ],
            ]
        );
    }

    /**
     * @param array<mixed> $expectedSuites
     */
    private function assertListResponse(ResponseInterface $response, array $expectedSuites): void
    {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $data = json_decode($response->getBody()->getContents(), true);

        self::assertIsArray($data);
        self::assertArrayHasKey('suites', $data);

        $suitesData = $data['suites'];
        self::assertIsArray($suitesData);
        self::assertSame($expectedSuites, $suitesData);
    }
}
