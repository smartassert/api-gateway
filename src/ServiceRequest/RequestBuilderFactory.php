<?php

declare(strict_types=1);

namespace App\ServiceRequest;

use Psr\Http\Message\StreamFactoryInterface;

readonly class RequestBuilderFactory
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $url
     */
    public function create(string $method, string $url): RequestBuilder
    {
        return new RequestBuilder($this->streamFactory, $method, $url);
    }
}
