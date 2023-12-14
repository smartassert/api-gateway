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

abstract class AbstractDeleteTest extends AbstractApplicationTestCase
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
    public function testDeleteUnauthorizedUser(?string $token): void
    {
        $id = (string) new Ulid();
        $response = $this->applicationClient->makeDeleteSuiteRequest($token, $id);

        self::assertSame(401, $response->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $response = $this->applicationClient->makeDeleteSuiteRequest($apiKey['key'], $suiteId);

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createSuiteDataProvider
     *
     * @param non-empty-string[] $tests
     */
    public function testDeleteSuccess(string $label, array $tests): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $suiteId = $this->createSuite($apiKey['key'], $sourceId, $label, $tests);

        $response = $this->applicationClient->makeDeleteSuiteRequest($apiKey['key'], $suiteId);

        $this->assertDeletedSuite($response, $sourceId, $label, $tests);
    }
}
