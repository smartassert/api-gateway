<?php

declare(strict_types=1);

namespace App\Tests\Application\Suite;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\CreateSourceTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;
    use AssertSuiteTrait;
    use CreateSourceTrait;
    use CreateSuiteDataProviderTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateSuiteRequest(
            $token,
            md5((string) rand()),
            md5((string) rand()),
            []
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testCreateSourceNotFound(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = (string) new Ulid();
        \assert('' !== $sourceId);

        $label = md5((string) rand());
        $tests = [];

        $response = $this->applicationClient->makeCreateSuiteRequest(
            $apiKey['key'],
            $sourceId,
            $label,
            $tests,
        );

        self::assertSame(403, $response->getStatusCode());
    }

    public function testCreateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));

        $label = '';
        $tests = [];

        $response = $this->applicationClient->makeCreateSuiteRequest(
            $apiKey['key'],
            $sourceId,
            $label,
            $tests,
        );

        $this->assertBadRequest(
            $response,
            'wrong_size',
            [
                'name' => 'label',
                'value' => '',
                'requirements' => [
                    'data_type' => 'string',
                    'size' => ['minimum' => 1, 'maximum' => 255],
                ],
            ]
        );
    }

    public function testCreateDuplicateLabel(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $label = md5((string) rand());
        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));

        $firstCreateResponse = $this->applicationClient->makeCreateSuiteRequest(
            $apiKey['key'],
            $sourceId,
            $label,
            [
                'test1.yaml',
            ]
        );
        self::assertSame(200, $firstCreateResponse->getStatusCode());

        $secondCreateResponse = $this->applicationClient->makeCreateSuiteRequest(
            $apiKey['key'],
            $sourceId,
            $label,
            [
                'test2.yaml',
            ]
        );

        $this->assertDuplicateObjectResponse($secondCreateResponse, 'label', $label);
    }

    /**
     * @dataProvider createSuiteDataProvider
     *
     * @param non-empty-string[] $tests
     */
    public function testCreateSuccess(string $label, array $tests): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $sourceId = $this->createFileSource($apiKey['key'], md5((string) rand()));
        $response = $this->applicationClient->makeCreateSuiteRequest($apiKey['key'], $sourceId, $label, $tests);

        $this->assertRetrievedSuite($response, $sourceId, $label, $tests);
    }
}
