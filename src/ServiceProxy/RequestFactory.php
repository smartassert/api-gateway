<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use GuzzleHttp\Psr7\Request as HttpRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

readonly class RequestFactory
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function create(Service $service, Request $inbound): RequestInterface
    {
        $outbound = new HttpRequest($inbound->getMethod(), $this->createRequestUrl($service, $inbound));

        $outbound = $this->setRequestAuthorization($inbound, $outbound);

        return $this->setRequestContent($inbound, $outbound);
    }

    private function createRequestUrl(Service $service, Request $inbound): string
    {
        return $service->getBaseUrl() . preg_replace('#^/' . $service->getName() . '#', '', $inbound->getRequestUri());
    }

    private function setRequestAuthorization(Request $inbound, RequestInterface $outbound): RequestInterface
    {
        if ($inbound->headers->has('authorization')) {
            $outbound = $outbound->withHeader('authorization', (string) $inbound->headers->get('authorization'));
        }

        return $outbound;
    }

    private function setRequestContent(Request $inbound, RequestInterface $outbound): RequestInterface
    {
        $inboundContentType = (string) $inbound->headers->get('content-type');
        $outbound = $outbound->withHeader('content-type', $inboundContentType);

        $body = 'application/x-www-form-urlencoded' === $inboundContentType
            ? http_build_query($inbound->request->all())
            : (string) $inbound->getContent();

        return $outbound->withBody($this->streamFactory->createStream($body));
    }
}
