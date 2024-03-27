<?php

declare(strict_types=1);

namespace App\Tests\Application\JobCoordinator;

use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractListTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeListJobCoordinatorJobsRequest($token, (string) new Ulid());

        self::assertSame(401, $response->getStatusCode());
    }

    /**
     * @dataProvider badMethodDataProvider
     */
    public function testListBadMethod(string $method): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeListJobCoordinatorJobsRequest(
            $apiKey['key'],
            (string) new Ulid(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public function badMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'delete',
            ],
        ];
    }

    public function testListSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $user1ApiKey = $apiKeyProvider->get('user1@example.com');
        $user2ApiKey = $apiKeyProvider->get('user2@example.com');

        $suite1Id = (string) new Ulid();
        $suite2Id = (string) new Ulid();

        $jobSummaries = [];

        $jobSummaries[] = $this->getJobSummaryFromResponse(
            $this->applicationClient->makeCreateJobCoordinatorJobRequest(
                $user1ApiKey['key'],
                $suite1Id,
                rand(1, 10000)
            )
        );

        $jobSummaries[] = $this->getJobSummaryFromResponse(
            $this->applicationClient->makeCreateJobCoordinatorJobRequest(
                $user1ApiKey['key'],
                $suite2Id,
                rand(1, 10000)
            )
        );

        $jobSummaries[] = $this->getJobSummaryFromResponse(
            $this->applicationClient->makeCreateJobCoordinatorJobRequest(
                $user1ApiKey['key'],
                $suite1Id,
                rand(1, 10000)
            )
        );

        $jobSummaries[] = $this->getJobSummaryFromResponse(
            $this->applicationClient->makeCreateJobCoordinatorJobRequest(
                $user2ApiKey['key'],
                $suite1Id,
                rand(1, 10000)
            )
        );

        $response = $this->applicationClient->makeListJobCoordinatorJobsRequest($user1ApiKey['key'], $suite1Id);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $expected = [
            $jobSummaries[2],
            $jobSummaries[0],
        ];
        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertSame($expected, $responseData);
    }

    /**
     * @return array<mixed>
     */
    private function getJobSummaryFromResponse(ResponseInterface $response): array
    {
        $responseData = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($responseData));

        return [
            'id' => $responseData['id'] ?? null,
            'suite_id' => $responseData['suite_id'] ?? null,
            'maximum_duration_in_seconds' => $responseData['maximum_duration_in_seconds'] ?? null,
            'created_at' => $responseData['created_at'] ?? null,
        ];
    }
}
