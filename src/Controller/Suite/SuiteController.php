<?php

declare(strict_types=1);

namespace App\Controller\Suite;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{serviceName<[a-z]+>}/suite', name: 'suite_')]
readonly class SuiteController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>?}', name: 'act', methods: ['POST', 'GET', 'PUT', 'DELETE'])]
    public function act(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: 's', name: 'list', methods: ['GET'])]
    public function list(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
