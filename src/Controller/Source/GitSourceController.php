<?php

declare(strict_types=1);

namespace App\Controller\Source;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/{serviceName<[a-z]+>}/git-source', name: 'git_source_')]
readonly class GitSourceController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(path: '/{sourceId<[A-Z90-9]{26}>?}', name: 'act', methods: ['POST', 'PUT'])]
    public function act(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
