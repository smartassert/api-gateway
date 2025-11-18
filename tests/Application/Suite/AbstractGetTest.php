<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\CreateSuiteTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;
    use AssertSuiteTrait;
    use CreateSourceTrait;
    use CreateSuiteTrait;
    use CreateSuiteDataProviderTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testGetUnauthorizedUser(?string $token): void
    {
        $id = (string) new Ulid();
        $response = $this->applicationClient->makeGetSuiteRequest($token, $id);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testGetNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $suiteId = (string) new Ulid();

        $label = md5((string) rand());
        $tests = [];

        $response = $this->applicationClient->makeGetSuiteRequest($apiKey['key'], $suiteId);

        self::assertSame(403, $response->getStatusCode());
    }

    /**
     * @dataProvider createSuiteDataProvider
     *
     * @param non-empty-string[] $tests
     */
    public function testGetSuccess(string $label, array $tests): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, $label, $tests);

        $response = $this->applicationClient->makeGetSuiteRequest($apiKey['key'], $suiteId);

        $this->assertRetrievedSuite($response, $sourceId, $label, $tests);
    }
}
