<?php

declare(strict_types=1);

namespace App\Response;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TransparentResponse extends SymfonyResponse
{
    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            $response->getBody()->getContents(),
            $response->getStatusCode(),
            ['content-type' => $response->getHeaderLine('content-type')]
        );
    }
}
