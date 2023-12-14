<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use App\Exception\ServiceException;
use App\Response\EmptyResponse;
use App\Response\ErrorResponse;
use App\Response\TransparentResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class ServiceProxy
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactory $requestFactory,
    ) {
    }

    /**
     * @param non-empty-string $successContentType
     *
     * @throws ServiceException
     */
    public function proxy(
        Service $service,
        Request $inbound,
        string $successContentType = 'application/json',
    ): Response {
        $outbound = $this->requestFactory->create($service, $inbound);

        try {
            $response = $this->httpClient->sendRequest($outbound);
        } catch (ClientExceptionInterface $e) {
            throw new ServiceException($service->getName(), $e);
        }

        $statusCode = $response->getStatusCode();
        if (in_array($statusCode, [401, 404])) {
            return new EmptyResponse($statusCode);
        }

        $contentType = $response->getHeaderLine('content-type');
        if ('' === $contentType) {
            return new EmptyResponse($statusCode);
        }

        if (
            (200 === $statusCode && str_starts_with($contentType, $successContentType))
            || ('application/json' === $contentType)
        ) {
            return new TransparentResponse($response);
        }

        return new ErrorResponse(
            'service-communication-failure',
            500,
            [
                'service' => $service->getName(),
                'code' => $statusCode,
                'reason' => $response->getReasonPhrase(),
                'expected_content_type' => $successContentType,
                'actual_content_type' => $contentType,
            ]
        );
    }
}
