<?php

declare(strict_types=1);

namespace App\Tests\Application;

trait CreateSuiteTrait
{
    /**
     * @param string[] $tests
     *
     * @return non-empty-string
     */
    public function createSuite(string $apiKey, string $sourceId, string $label, array $tests): string
    {
        $response = $this->applicationClient->makeCreateSuiteRequest($apiKey, $sourceId, $label, $tests);

        \assert(200 === $response->getStatusCode());

        $responseData = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($responseData));

        $sourceData = $responseData['suite'];
        \assert(is_array($sourceData));

        $id = $sourceData['id'] ?? null;
        \assert(is_string($id) && '' !== $id);

        return $id;
    }
}
