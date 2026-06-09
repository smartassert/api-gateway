<?php

declare(strict_types=1);

namespace App\Tests\Application\Results;

use App\Security\ApiTokenProvider;
use App\Tests\Application\AbstractApplicationTestCase;
use App\Tests\Application\AssertBadRequestTrait;
use App\Tests\Application\UnauthorizedUserDataProviderTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ResultsClient\AddEventClientInterface;
use SmartAssert\ResultsClient\ClientInterface as ResultsClient;
use SmartAssert\ResultsClient\Model\Event;
use SmartAssert\ResultsClient\Model\ResourceReference;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use Symfony\Component\Uid\Ulid;

abstract class AbstractListEventsTest extends AbstractApplicationTestCase
{
    use UnauthorizedUserDataProviderTrait;
    use AssertBadRequestTrait;

    #[DataProvider('unauthorizedUserDataProvider')]
    public function testListEventsUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeListResultsEventsRequest(
            $token,
            (string) new Ulid(),
            null,
            null,
        );

        self::assertSame(401, $response->getStatusCode());
    }

    #[DataProvider('badMethodDataProvider')]
    public function testListEventsBadMethod(string $method): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $apiKey = $apiKeyProvider->get('user1@example.com');

        $response = $this->applicationClient->makeListResultsEventsRequest(
            $apiKey['key'],
            (string) new Ulid(),
            null,
            null,
            $method
        );

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @return array<mixed>
     */
    public static function badMethodDataProvider(): array
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

    public function testListEventsSuccess(): void
    {
        $apiKeyProvider = self::getContainer()->get(ApiKeyProvider::class);
        \assert($apiKeyProvider instanceof ApiKeyProvider);
        $user1ApiKey = $apiKeyProvider->get('user1@example.com');

        $jobLabel = (string) new Ulid();

        $nonExistentResultsJobResponse = $this->applicationClient->makeListResultsEventsRequest(
            $user1ApiKey['key'],
            $jobLabel,
            null,
            null,
        );

        self::assertSame(200, $nonExistentResultsJobResponse->getStatusCode());
        self::assertSame('application/json', $nonExistentResultsJobResponse->getHeaderLine('content-type'));
        self::assertSame('[]', $nonExistentResultsJobResponse->getBody()->getContents());

        $apiTokenProvider = self::getContainer()->get(ApiTokenProvider::class);
        \assert($apiTokenProvider instanceof ApiTokenProvider);
        $user1ApiToken = $apiTokenProvider->get($user1ApiKey['key']);
        \assert(is_string($user1ApiToken) && '' !== $user1ApiToken);

        $resultsClient = self::getContainer()->get(ResultsClient::class);
        \assert($resultsClient instanceof ResultsClient);

        $resultsJob = $resultsClient->createJob($user1ApiToken, $jobLabel);

        $existentResultsJobResponse = $this->applicationClient->makeListResultsEventsRequest(
            $user1ApiKey['key'],
            $resultsJob->label,
            null,
            null,
        );

        self::assertSame(200, $existentResultsJobResponse->getStatusCode());
        self::assertSame('application/json', $existentResultsJobResponse->getHeaderLine('content-type'));
        self::assertSame('[]', $existentResultsJobResponse->getBody()->getContents());

        $addEventClient = self::getContainer()->get(AddEventClientInterface::class);
        \assert($addEventClient instanceof AddEventClientInterface);

        $event1 = new Event(
            1,
            'event_type_1',
            new ResourceReference('event_label_1', 'event_reference_1'),
            []
        );

        $addEventClient->add($resultsJob->authenticator, $event1);

        $event2 = new Event(
            2,
            'event_type_2',
            new ResourceReference('event_label_2', 'event_reference_2'),
            []
        );

        $addEventClient->add($resultsJob->authenticator, $event2);

        $existentEventsResponse = $this->applicationClient->makeListResultsEventsRequest(
            $user1ApiKey['key'],
            $resultsJob->label,
            null,
            null,
        );

        self::assertSame(200, $existentEventsResponse->getStatusCode());
        self::assertSame('application/json', $existentEventsResponse->getHeaderLine('content-type'));

        self::assertJsonStringEqualsJsonString(
            (string) json_encode([
                $event1->withJob($jobLabel)->toArray(),
                $event2->withJob($jobLabel)->toArray(),
            ]),
            $existentEventsResponse->getBody()->getContents(),
        );
    }
}
