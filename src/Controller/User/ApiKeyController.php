<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class ApiKeyController
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
    #[Route('/user/apikey{action}', name: 'user_apikey_act', requirements: ['action' => '.*'], methods: ['GET'])]
    public function list(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }
}
