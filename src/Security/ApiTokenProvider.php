<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use GuzzleHttp\Psr7\Request;

readonly class ApiTokenProvider
{
    public function __construct(
        private ServiceProxy $serviceProxy,
        private Service $userService,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @return ?string
     *
     * @throws ServiceException
     */
    public function get(string $apiKey): ?string
    {
        $createTokenResponse = $this->serviceProxy->proxy(
            $this->userService,
            new Request('POST', $this->userService->createUrl('/api-token/create'), ['authorization' => $apiKey]),
            ['application/json']
        );

        $responseData = json_decode((string) $createTokenResponse->getContent(), true);
        $apiToken = null;

        if (is_array($responseData)) {
            $apiToken = $responseData['token'] ?? null;
            if (!is_string($apiToken)) {
                $apiToken = null;
            }
        }

        return $apiToken;
    }
}
