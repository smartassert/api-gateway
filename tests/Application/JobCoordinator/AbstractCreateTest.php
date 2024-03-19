<?php

declare(strict_types=1);

namespace App\Tests\Application\JobCoordinator;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractCreateTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeCreateJobCoordinatorJobRequest($token, (string) new Ulid(), rand());

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateJobCoordinatorJobRequest(
            $apiKey['key'],
            (string) new Ulid(),
            rand(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'delete',
            ],
        ];
    }

    public function testCreateBadRequest(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $response = $this->applicationClient->makeCreateJobCoordinatorJobRequest(
            $apiKey['key'],
            (string) new Ulid(),
            0
        );

        $this->assertBadRequest(
            $response,
            'wrong_size',
            [
                'name' => 'maximum_duration_in_seconds',
                'value' => 0,
                'requirements' => [
                    'data_type' => 'integer',
                    'size' => ['minimum' => 1, 'maximum' => 2147483647],
                ],
            ]
        );
    }

    public function testCreateSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user@example.com');

        $suiteId = (string) new Ulid();
        $maximumDurationInSeconds = rand(1, 10000);

        $response = $this->applicationClient->makeCreateJobCoordinatorJobRequest(
            $apiKey['key'],
            $suiteId,
            $maximumDurationInSeconds
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        self::assertTrue(Ulid::isValid($responseData['id']));
        self::assertSame($suiteId, $responseData['suite_id']);
        self::assertSame($maximumDurationInSeconds, $responseData['maximum_duration_in_seconds']);
    }
}
