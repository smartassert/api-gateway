<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ServiceException;
use App\ServiceProxy\RequestFactory;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

readonly class RequestHandler
{
    public function __construct(
        private RequestFactory $requestFactory,
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{serviceName<[a-z-]+>}/{action<.+>}')]
    public function handle(Service $service, Request $request): Response
    {
        $acceptableContentTypes = $request->getAcceptableContentTypes();

        return $this->serviceProxy->proxy(
            $service,
            $this->requestFactory->create($service, $request),
            [] === $acceptableContentTypes ? ['application/json'] : $acceptableContentTypes
        );
    }
}
