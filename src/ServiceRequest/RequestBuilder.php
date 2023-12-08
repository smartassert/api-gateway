<?php

declare(strict_types=1);

namespace App\ServiceRequest;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RequestBuilder
{
    private RequestInterface $request;

    public function __construct(
        private readonly StreamFactoryInterface $streamFactory,
        string $method,
        string $url
    ) {
        $this->request = new Request($method, $url);
    }

    public function get(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @param non-empty-string $token
     */
    public function withAuthorization(string $token): self
    {
        $this->setHeader('authorization', 'Bearer ' . $token);

        return $this;
    }

    /**
     * @param array<mixed> $payload
     */
    public function withPayload(array $payload): self
    {
        $encodedPayload = http_build_query($payload);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            return $this->withBody($encodedPayload, 'application/x-www-form-urlencoded');
        }

        $this->request = $this->request->withUri($this->request->getUri()->withQuery($encodedPayload));

        return $this;
    }

    public function withBody(string $content, string $contentType): self
    {
        $this->setBody($content);
        $this->setHeader('content-type', $contentType);

        return $this;
    }

    /**
     * @param non-empty-string $name
     */
    private function setHeader(string $name, string $value): void
    {
        $this->request = $this->request->withHeader($name, $value);
    }

    private function setBody(string $body): void
    {
        $this->request = $this->request->withBody($this->streamFactory->createStream($body));
    }
}
