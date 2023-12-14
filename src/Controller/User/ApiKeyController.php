<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class ApiKeyController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
    ) {
    }

    /**
     * @throws ServiceException
     */
    #[Route(
        path: '/{serviceName<[a-z]+>}/apikey{action}',
        name: 'user_apikey_act',
        requirements: ['action' => '.*'],
        methods: ['GET']
    )]
    public function list(Service $service, Request $request): Response
    {
        return $this->serviceProxy->proxy($service, $request);
    }
}
