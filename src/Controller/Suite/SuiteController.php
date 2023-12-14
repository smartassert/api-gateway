<?php

declare(strict_types=1);

namespace App\Controller\Suite;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/source/suite', name: 'suite_')]
readonly class SuiteController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
        private ServiceCollection $serviceCollection,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route(path: '/{suiteId<[A-Z90-9]{26}>?}', name: 'act', methods: ['POST', 'GET', 'PUT', 'DELETE'])]
    public function act(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('source'), $request);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route(path: 's', name: 'list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('source'), $request);
    }
}
