<?php

declare(strict_types=1);

namespace App\ServiceExceptionResponseFactory;

use App\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

interface HandlerInterface
{
    public function handle(ServiceException $serviceException): ?Response;
}
