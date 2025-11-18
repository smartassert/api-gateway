<?php

declare(strict_types=1);

namespace App\Tests\Application\JobCoordinator;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testGetUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeGetJobCoordinatorJobRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    #[DataProvider('getBadMethodDataProvider')]
    public function testGetBadMethod(string $method): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeGetJobCoordinatorJobRequest(
            $apiKey['key'],
            (string) new Ulid(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public static function getBadMethodDataProvider(): array
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

    public function testGetSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $suiteId = (string) new Ulid();
        $maximumDurationInSeconds = rand(1, 10000);

        $createResponse = $this->applicationClient->makeCreateJobCoordinatorJobRequest(
            $apiKey['key'],
            $suiteId,
            $maximumDurationInSeconds
        );
        self::assertSame(200, $createResponse->getStatusCode());

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        \assert(is_array($createResponseData));

        $jobId = $createResponseData['id'] ?? null;
        \assert(is_string($jobId));

        $response = $this->applicationClient->makeGetJobCoordinatorJobRequest($apiKey['key'], $jobId);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        self::assertIsString($responseData['id']);
        self::assertTrue(Ulid::isValid($responseData['id']));
        self::assertSame($suiteId, $responseData['suite_id']);
        self::assertSame($maximumDurationInSeconds, $responseData['maximum_duration_in_seconds']);
    }
}
