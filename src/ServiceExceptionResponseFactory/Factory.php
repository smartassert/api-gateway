<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

readonly class Factory
{
    /**
     * @param iterable<HandlerInterface> $handlers
     */
    public function __construct(private iterable $handlers)
    {
    }

    public function create(ServiceException $serviceException): ?Response
    {
        foreach ($this->handlers as $handler) {
            $response = $handler->handle($serviceException);

            if ($response instanceof Response) {
                return $response;
            }
        }

        return null;
    }
}
