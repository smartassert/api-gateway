<?php

declare(strict_types=1);

namespace App\Exception;

use Psr\Http\Message\ResponseInterface;

class UnexpectedServiceResponseException extends \Exception
{
    public function __construct(
        public readonly string $serviceName,
        public readonly string $expectedContentType,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct('Unexpected response from "' . $serviceName . '" service.', $response->getStatusCode());
    }

    /**
     * @return array{
     *     service: string,
     *     code: int,
     *     reason: string,
     *     expected_content_type: string,
     *     actual_content_type: ?string,
     * }
     */
    public function toArray(): array
    {
        $responseContentType = $this->response->hasHeader('content-type')
            ? $this->response->getHeaderLine('content-type')
            : null;

        return [
            'service' => $this->serviceName,
            'code' => $this->getCode(),
            'reason' => $this->response->getReasonPhrase(),
            'expected_content_type' => $this->expectedContentType,
            'actual_content_type' => $responseContentType,
        ];
    }
}
