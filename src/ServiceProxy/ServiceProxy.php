<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use App\Exception\ServiceException;
use App\Response\ErrorResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

readonly class ServiceProxy
{
    public function __construct(
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @param string[] $acceptableContentTypes
     *
     * @throws ServiceException
     */
    public function proxy(Service $service, RequestInterface $outbound, array $acceptableContentTypes): Response
    {
        try {
            $response = $this->httpClient->sendRequest($outbound);
        } catch (ClientExceptionInterface $e) {
            throw new ServiceException($service->getName(), $e);
        }

        $statusCode = $response->getStatusCode();
        if (in_array($statusCode, [401, 404])) {
            return new Response(null, $statusCode, ['content-type' => null]);
        }

        $contentType = $response->getHeaderLine('content-type');
        if ('' === $contentType) {
            return new Response(null, $statusCode, ['content-type' => null]);
        }

        if (405 === $statusCode && 'application/json' !== $contentType) {
            return new Response(null, $statusCode, ['content-type' => null]);
        }

        if (
            (200 === $statusCode && $this->responseHasAcceptableContentType($response, $acceptableContentTypes))
            || ('application/json' === $contentType)
        ) {
            return new Response(
                $response->getBody()->getContents(),
                $response->getStatusCode(),
                ['content-type' => $response->getHeaderLine('content-type')]
            );
        }

        return new ErrorResponse(
            'service-communication-failure',
            500,
            [
                'service' => $service->getName(),
                'code' => $statusCode,
                'reason' => $response->getReasonPhrase(),
                'expected_content_type' => implode(', ', $acceptableContentTypes),
                'actual_content_type' => $contentType,
            ]
        );
    }

    /**
     * @param string[] $acceptableContentTypes
     */
    private function responseHasAcceptableContentType(
        ResponseInterface $response,
        array $acceptableContentTypes
    ): bool {
        $contentType = $response->getHeaderLine('content-type');

        foreach ($acceptableContentTypes as $acceptableContentType) {
            if (str_starts_with($contentType, $acceptableContentType)) {
                return true;
            }
        }

        return false;
    }
}
